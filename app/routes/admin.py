"""
app/routes/admin.py — Panel Admin Toko Rini
Semua route dilindungi oleh decorator @login_required.
CRUD produk + upload gambar. Semua query adalah Raw SQL.
"""
import os
import uuid
from functools import wraps
from flask import (Blueprint, render_template, request, redirect,
                   url_for, session, flash, current_app)
from werkzeug.utils import secure_filename
from app.db import query_db

admin_bp = Blueprint('admin', __name__, template_folder='../templates/admin')

ALLOWED_EXTENSIONS = {'jpg', 'jpeg', 'png', 'webp'}


# ─────────────────────────────────────────────
# Helper
# ─────────────────────────────────────────────
def login_required(f):
    """Decorator: redirect ke /login jika belum autentikasi."""
    @wraps(f)
    def decorated(*args, **kwargs):
        if not session.get('admin_id'):
            flash('Silakan login terlebih dahulu.', 'warning')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated


def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS


def save_image(file):
    """Simpan file gambar ke static/images/, kembalikan nama file unik."""
    ext      = file.filename.rsplit('.', 1)[1].lower()
    filename = f"{uuid.uuid4().hex}.{ext}"
    path     = os.path.join(current_app.config['UPLOAD_FOLDER'], filename)
    file.save(path)
    return filename


# ─────────────────────────────────────────────
# DASHBOARD
# ─────────────────────────────────────────────
@admin_bp.route('/')
@login_required
def dashboard():
    """
    Ringkasan data menggunakan SQL COUNT() dan SUM().
    Menampilkan: total produk, total pesanan, total pendapatan, pesanan pending.
    """
    stats = query_db("""
        SELECT
            (SELECT COUNT(*) FROM products  WHERE is_active = 1) AS total_products,
            (SELECT COUNT(*) FROM orders)                         AS total_orders,
            (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'completed') AS total_revenue,
            (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending_orders
    """, one=True)

    # 5 pesanan terbaru
    recent_orders = query_db("""
        SELECT id, customer_name, total_price, status, created_at
        FROM   orders
        ORDER  BY created_at DESC
        LIMIT  5
    """)

    return render_template('admin/dashboard.html', stats=stats, recent_orders=recent_orders)


# ─────────────────────────────────────────────
# PRODUK — READ (list)
# ─────────────────────────────────────────────
@admin_bp.route('/produk')
@login_required
def produk_list():
    """Daftar semua produk dengan JOIN ke categories."""
    products = query_db("""
        SELECT p.id, p.name, p.price, p.stock, p.image, p.is_active,
               c.name AS category_name
        FROM   products   p
        JOIN   categories c ON c.id = p.category_id
        ORDER  BY p.created_at DESC
    """)
    return render_template('admin/produk_list.html', products=products)


# ─────────────────────────────────────────────
# PRODUK — CREATE
# ─────────────────────────────────────────────
@admin_bp.route('/produk/tambah', methods=['GET', 'POST'])
@login_required
def produk_tambah():
    categories = query_db("SELECT id, name FROM categories ORDER BY name")

    if request.method == 'POST':
        name        = request.form.get('name', '').strip()
        category_id = request.form.get('category_id')
        description = request.form.get('description', '').strip()
        price       = request.form.get('price', 0)
        stock       = request.form.get('stock', 0)
        image_file  = request.files.get('image')

        # Validasi sederhana
        if not name or not category_id or not price:
            flash('Nama, kategori, dan harga wajib diisi.', 'danger')
            return render_template('admin/produk_form.html',
                                   categories=categories, action='tambah')

        # Upload gambar jika ada
        image_name = None
        if image_file and image_file.filename and allowed_file(image_file.filename):
            image_name = save_image(image_file)

        query_db("""
            INSERT INTO products (category_id, name, description, price, stock, image)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (category_id, name, description, price, stock, image_name), commit=True)

        flash(f'Produk "{name}" berhasil ditambahkan.', 'success')
        return redirect(url_for('admin.produk_list'))

    return render_template('admin/produk_form.html',
                           categories=categories, action='tambah', produk=None)


# ─────────────────────────────────────────────
# PRODUK — UPDATE (Edit)
# ─────────────────────────────────────────────
@admin_bp.route('/produk/edit/<int:produk_id>', methods=['GET', 'POST'])
@login_required
def produk_edit(produk_id):
    produk     = query_db("SELECT * FROM products WHERE id = %s", (produk_id,), one=True)
    categories = query_db("SELECT id, name FROM categories ORDER BY name")

    if not produk:
        flash('Produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.produk_list'))

    if request.method == 'POST':
        name        = request.form.get('name', '').strip()
        category_id = request.form.get('category_id')
        description = request.form.get('description', '').strip()
        price       = request.form.get('price', 0)
        stock       = request.form.get('stock', 0)
        is_active   = 1 if request.form.get('is_active') else 0
        image_file  = request.files.get('image')

        # Ganti gambar jika ada upload baru
        image_name = produk['image']
        if image_file and image_file.filename and allowed_file(image_file.filename):
            # Hapus gambar lama
            if image_name:
                old_path = os.path.join(current_app.config['UPLOAD_FOLDER'], image_name)
                if os.path.exists(old_path):
                    os.remove(old_path)
            image_name = save_image(image_file)

        query_db("""
            UPDATE products
            SET    category_id = %s, name = %s, description = %s,
                   price = %s, stock = %s, image = %s, is_active = %s
            WHERE  id = %s
        """, (category_id, name, description, price, stock,
              image_name, is_active, produk_id), commit=True)

        flash(f'Produk "{name}" berhasil diperbarui.', 'success')
        return redirect(url_for('admin.produk_list'))

    return render_template('admin/produk_form.html',
                           categories=categories, action='edit', produk=produk)


# ─────────────────────────────────────────────
# PRODUK — DELETE
# ─────────────────────────────────────────────
@admin_bp.route('/produk/hapus/<int:produk_id>', methods=['POST'])
@login_required
def produk_hapus(produk_id):
    produk = query_db("SELECT * FROM products WHERE id = %s", (produk_id,), one=True)
    if produk:
        # Hapus file gambar dari disk
        if produk['image']:
            img_path = os.path.join(current_app.config['UPLOAD_FOLDER'], produk['image'])
            if os.path.exists(img_path):
                os.remove(img_path)
        # Soft delete (set is_active = 0) agar order_items history tetap valid
        query_db("UPDATE products SET is_active = 0 WHERE id = %s",
                 (produk_id,), commit=True)
        flash(f'Produk "{produk["name"]}" berhasil dihapus.', 'success')
    return redirect(url_for('admin.produk_list'))


# ─────────────────────────────────────────────
# PESANAN — List & Detail
# ─────────────────────────────────────────────
@admin_bp.route('/pesanan')
@login_required
def pesanan_list():
    orders = query_db("""
        SELECT id, customer_name, customer_phone, total_price, status, created_at
        FROM   orders
        ORDER  BY created_at DESC
    """)
    return render_template('admin/pesanan_list.html', orders=orders)


@admin_bp.route('/pesanan/<int:order_id>')
@login_required
def pesanan_detail(order_id):
    """
    Detail pesanan menggunakan JOIN antara order_items dan products.
    """
    order = query_db("SELECT * FROM orders WHERE id = %s", (order_id,), one=True)
    if not order:
        flash('Pesanan tidak ditemukan.', 'danger')
        return redirect(url_for('admin.pesanan_list'))

    items = query_db("""
        SELECT oi.product_name, oi.price, oi.quantity, oi.subtotal,
               p.image
        FROM   order_items oi
        LEFT   JOIN products p ON p.id = oi.product_id
        WHERE  oi.order_id = %s
    """, (order_id,))

    return render_template('admin/pesanan_detail.html', order=order, items=items)


@admin_bp.route('/pesanan/<int:order_id>/status', methods=['POST'])
@login_required
def pesanan_status(order_id):
    new_status = request.form.get('status')
    valid = ('pending', 'confirmed', 'completed', 'cancelled')
    if new_status in valid:
        query_db("UPDATE orders SET status = %s WHERE id = %s",
                 (new_status, order_id), commit=True)
        flash('Status pesanan diperbarui.', 'success')
    return redirect(url_for('admin.pesanan_detail', order_id=order_id))
