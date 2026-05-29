"""
app/routes/public.py — Halaman Publik Toko Rini
Beranda, Katalog Produk, Keranjang (JS), dan Checkout (POST → WhatsApp).
Semua data produk dan kategori diambil dari MySQL via Raw SQL.
"""
import json
from flask import (Blueprint, render_template, request,
                   redirect, url_for, jsonify)
from app.db import query_db

public_bp = Blueprint('public', __name__)

# ─────────────────────────────────────────────
# BERANDA
# ─────────────────────────────────────────────
@public_bp.route('/')
def index():
    """
    Menampilkan beranda: profil toko, banner, peta, dan produk unggulan.
    Query JOIN: products ← categories (untuk menampilkan nama kategori).
    """
    featured = query_db("""
        SELECT p.id, p.name, p.price, p.image, c.name AS category_name
        FROM   products  p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.is_active = 1
        ORDER  BY p.created_at DESC
        LIMIT  8
    """)
    return render_template('index.html', featured=featured)


# ─────────────────────────────────────────────
# KATALOG PRODUK (Pencarian + Filter Kategori)
# ─────────────────────────────────────────────
@public_bp.route('/produk')
def produk():
    """
    Daftar produk dengan fitur:
    - Pencarian nama   : SQL LIKE %keyword%
    - Filter kategori  : SQL WHERE category_id = ?
    Query JOIN produk dengan kategori untuk menampilkan badge kategori.
    """
    search      = request.args.get('q', '').strip()
    cat_id      = request.args.get('cat', '').strip()
    categories  = query_db("SELECT id, name FROM categories ORDER BY name")

    # Bangun query secara dinamis (semua parameterised — aman SQL injection)
    base_sql = """
        SELECT p.id, p.name, p.price, p.stock, p.image,
               c.name AS category_name, c.id AS category_id
        FROM   products   p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.is_active = 1
    """
    params = []

    if search:
        base_sql += " AND p.name LIKE %s"
        params.append(f'%{search}%')

    if cat_id and cat_id.isdigit():
        base_sql += " AND p.category_id = %s"
        params.append(int(cat_id))

    base_sql += " ORDER BY p.name"

    products = query_db(base_sql, tuple(params))

    return render_template('produk.html',
                           products=products,
                           categories=categories,
                           search=search,
                           active_cat=cat_id)


# ─────────────────────────────────────────────
# CHECKOUT — Simpan Order & Arahkan ke WhatsApp
# ─────────────────────────────────────────────
@public_bp.route('/checkout', methods=['POST'])
def checkout():
    """
    Menerima data keranjang (JSON dari JS), menyimpan ke orders + order_items,
    lalu mengalihkan pengguna ke WhatsApp dengan detail pesanan.

    Flow:
    1. INSERT INTO orders → dapatkan order_id via LAST_INSERT_ID()
    2. INSERT INTO order_items (loop tiap item)
    3. Redirect ke wa.me dengan pesan ringkasan
    """
    data = request.get_json()
    if not data:
        return jsonify({'error': 'Data tidak valid'}), 400

    name    = data.get('name', '').strip()
    phone   = data.get('phone', '').strip()
    address = data.get('address', '').strip()
    notes   = data.get('notes', '').strip()
    items   = data.get('items', [])

    if not name or not phone or not address or not items:
        return jsonify({'error': 'Lengkapi semua field'}), 400

    # Hitung total dari DB (jangan percaya harga dari client)
    total = 0
    validated_items = []
    for item in items:
        prod = query_db(
            "SELECT id, name, price, stock FROM products WHERE id = %s AND is_active = 1",
            (item['id'],),
            one=True
        )
        if not prod:
            return jsonify({'error': f"Produk id {item['id']} tidak ditemukan"}), 400
        qty = max(1, int(item.get('qty', 1)))
        subtotal = prod['price'] * qty
        total += subtotal
        validated_items.append({
            'product_id':   prod['id'],
            'product_name': prod['name'],
            'price':        prod['price'],
            'quantity':     qty,
            'subtotal':     subtotal,
        })

    # INSERT orders
    order_id = query_db(
        """
        INSERT INTO orders (customer_name, customer_phone, customer_address, total_price, notes)
        VALUES (%s, %s, %s, %s, %s)
        """,
        (name, phone, address, total, notes),
        commit=True
    )

    # INSERT order_items (menggunakan order_id yang baru saja dibuat)
    for it in validated_items:
        query_db(
            """
            INSERT INTO order_items
                (order_id, product_id, product_name, price, quantity, subtotal)
            VALUES (%s, %s, %s, %s, %s, %s)
            """,
            (order_id, it['product_id'], it['product_name'],
             it['price'], it['quantity'], it['subtotal']),
            commit=True
        )

    # Bangun pesan WhatsApp
    wa_number = '6281234567890'   # ← ganti dengan nomor WA pemilik toko
    lines = [f"Halo Toko Rini, saya ingin memesan:\n"]
    for it in validated_items:
        lines.append(f"- {it['product_name']} x{it['quantity']} = Rp{it['subtotal']:,.0f}")
    lines.append(f"\n*Total: Rp{total:,.0f}*")
    lines.append(f"Nama: {name}")
    lines.append(f"Alamat: {address}")
    if notes:
        lines.append(f"Catatan: {notes}")
    lines.append(f"\nNo. Pesanan: #{order_id}")

    import urllib.parse
    wa_message = urllib.parse.quote('\n'.join(lines))
    wa_url      = f"https://wa.me/{wa_number}?text={wa_message}"

    return jsonify({'redirect': wa_url, 'order_id': order_id})
