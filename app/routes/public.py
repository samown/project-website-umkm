import json
import urllib.parse
from flask import Blueprint, current_app, jsonify, render_template, request
from app.db import query_db

public_bp = Blueprint('public', __name__)

# --- Constants ---
WA_NUMBER = "628123456789"
ACTIVE_STATUS = 1


# --- Utility Functions ---

def format_rupiah(amount):
    """Formats an integer amount into an Indonesian Rupiah string."""
    return f"Rp {amount:,}".replace(',', '.')

def generate_order_number():
    """Generates a sequential daily order number."""
    return query_db(
        "SELECT CONCAT('TK', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(COUNT(*) + 1, 3, '0')) AS order_number FROM orders WHERE DATE(created_at) = CURDATE()",
        one=True,
    )['order_number']

def handle_error_response(message, is_json_payload, status_code=400):
    """Standardizes JSON error responses based on request type."""
    if is_json_payload:
        return jsonify({'error': message}), status_code
    return jsonify({'success': False, 'message': message}), status_code

def get_active_categories(include_slug=False):
    """Fetches active categories from the database."""
    columns = 'id, name, slug' if include_slug else 'id, name'
    return query_db(f'SELECT {columns} FROM categories WHERE is_active = %s ORDER BY display_order', (ACTIVE_STATUS,))


# --- Routes ---

@public_bp.route('/')
def index():
    featured = query_db(
        'SELECT p.id, p.name, p.price, p.stock, p.image, c.name AS category_name '
        'FROM products p '
        'LEFT JOIN categories c ON p.category_id = c.id '
        'WHERE p.is_active = %s '
        'ORDER BY p.created_at DESC '
        'LIMIT 8',
        (ACTIVE_STATUS,)
    )
    return render_template('index.html', featured=featured)


@public_bp.route('/kontak')
def kontak():
    return render_template('public/kontak.html')


@public_bp.route('/api/categories')
def api_categories():
    categories = get_active_categories(include_slug=True)
    return jsonify(categories)


@public_bp.route('/api/products')
def api_products():
    products = query_db('SELECT id, name, price, stock, image FROM products WHERE is_active = %s LIMIT 100', (ACTIVE_STATUS,))
    return jsonify(products)


@public_bp.route('/katalog')
def katalog():
    search = request.args.get('search', '').strip()
    category_id = request.args.get('category_id', '').strip()

    categories = get_active_categories()

    sql = 'SELECT id, name, price, stock, image FROM products WHERE is_active = %s'
    params = [ACTIVE_STATUS]

    if search:
        sql += ' AND name LIKE %s'
        params.append(f'%{search}%')

    if category_id:
        sql += ' AND category_id = %s'
        params.append(category_id)

    sql += ' ORDER BY name ASC'

    products = query_db(sql, params)

    return render_template(
        'public/katalog.html',
        products=products,
        categories=categories,
        search=search,
        selected_category=category_id,
    )


@public_bp.route('/checkout', methods=['GET'])
def checkout_page():
    return render_template('public/checkout.html')


@public_bp.route('/checkout', methods=['POST'])
def process_checkout():
    try:
        payload = request.get_json(silent=True) or {}
        is_json_payload = bool(payload)
        form_data = request.form

        customer_name = (payload.get('name') or form_data.get('nama') or '').strip()
        customer_phone = (payload.get('phone') or form_data.get('whatsapp') or '').strip()
        customer_address = (payload.get('address') or '').strip() or 'Alamat belum diisi'
        notes = (payload.get('notes') or '').strip()

        if payload.get('items'):
            requested_items = payload.get('items', [])
        else:
            requested_items = []
            cart_data_raw = form_data.get('cart_data') or '[]'
            for item in json.loads(cart_data_raw):
                requested_items.append({
                    'id': item.get('id'),
                    'qty': item.get('qty', 1),
                    'name': item.get('nama')
                })

        if not customer_name or not customer_phone or not requested_items:
            return handle_error_response('Data checkout tidak lengkap.', is_json_payload)

        product_ids = [int(item.get('id')) for item in requested_items if item.get('id')]
        if not product_ids:
            return handle_error_response('Produk pada keranjang tidak valid.', is_json_payload)

        placeholders = ','.join(['%s'] * len(product_ids))
        products = query_db(
            f'SELECT id, name, price FROM products WHERE is_active = %s AND id IN ({placeholders})',
            (ACTIVE_STATUS, *product_ids),
        )
        product_map = {str(product['id']): product for product in products}

        order_items = []
        total_price = 0
        total_items = 0

        for item in requested_items:
            product = product_map.get(str(item.get('id')))
            qty = max(int(item.get('qty', 1)), 1)
            if not product:
                continue
            price = int(product['price'])
            subtotal = price * qty
            total_price += subtotal
            total_items += qty
            order_items.append({
                'product_id': product['id'],
                'product_name': product['name'],
                'price': price,
                'quantity': qty,
                'subtotal': subtotal,
            })

        if not order_items:
            return handle_error_response('Produk pada keranjang tidak ditemukan.', is_json_payload)

        order_number = generate_order_number()

        order_id = query_db(
            '''
            INSERT INTO orders (order_number, customer_name, customer_phone, customer_address, total_price, total_items, payment_method, status, notes)
            VALUES (%s, %s, %s, %s, %s, %s, 'cash', 'pending', %s)
            ''',
            (order_number, customer_name, customer_phone, customer_address, total_price, total_items, notes),
            commit=True,
        )

        for item in order_items:
            query_db(
                '''
                INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
                VALUES (%s, %s, %s, %s, %s, %s)
                ''',
                (
                    order_id,
                    item['product_id'],
                    item['product_name'],
                    item['price'],
                    item['quantity'],
                    item['subtotal'],
                ),
                commit=True,
            )

        pesan = [
            f"*KONFIRMASI PESANAN - {order_number}*",
            '',
            f"*Nama Pembeli:* {customer_name}",
            f"*No. WhatsApp:* {customer_phone}",
            '----------------------------------',
            '*Daftar Belanja:*',
        ]

        for item in order_items:
            pesan.append(f"- {item['product_name']} (x{item['quantity']}) : {format_rupiah(item['subtotal'])}")

        pesan.extend([
            '----------------------------------',
            f"*Total Akhir:* {format_rupiah(total_price)}",
            '',
            'Halo admin, saya sudah melakukan checkout. Mohon dibantu proses pesanan saya.',
        ])

        encoded_message = urllib.parse.quote("\n".join(pesan))
        whatsapp_url = f"https://wa.me/{WA_NUMBER}?text={encoded_message}"

        if is_json_payload:
            return jsonify({'redirect': whatsapp_url})

        return jsonify(
            {
                'success': True,
                'message': 'Pesanan kamu berhasil disimpan!',
                'redirect_url': whatsapp_url,
            }
        )

    except (ValueError, TypeError, KeyError, json.JSONDecodeError) as e:
        error = f'Data checkout tidak valid: {str(e)}'
        is_json_payload = bool(request.get_json(silent=True))
        return handle_error_response(error, is_json_payload)
    except Exception:
        current_app.logger.exception('Checkout failed')
        error = 'Terjadi kesalahan sistem. Silakan coba lagi.'
        is_json_payload = bool(request.get_json(silent=True))
        return handle_error_response(error, is_json_payload, 500)
