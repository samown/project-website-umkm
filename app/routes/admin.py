import os
import re
import uuid
from flask import Blueprint, render_template, request, redirect, url_for, flash, current_app
from werkzeug.utils import secure_filename
from app.db import query_db, insert_db, update_db, delete_db
from app.utils.decorators import login_required

admin_bp = Blueprint('admin', __name__, url_prefix='/admin')


def slugify(text):
    safe = re.sub(r'[^a-zA-Z0-9\s-]', '', text or '')
    safe = re.sub(r'\s+', '-', safe.strip()).lower()
    return safe or str(uuid.uuid4())


def allowed_file(filename):
    if '.' not in filename:
        return False
    ext = filename.rsplit('.', 1)[1].lower()
    return ext in current_app.config['ALLOWED_EXTENSIONS']


def save_image_file(file_storage):
    filename = secure_filename(file_storage.filename)
    ext = filename.rsplit('.', 1)[1].lower()
    unique_name = f"{uuid.uuid4().hex}.{ext}"
    file_path = os.path.join(current_app.config['UPLOAD_FOLDER'], unique_name)
    file_storage.save(file_path)
    return unique_name


def delete_image_file(filename):
    if not filename:
        return
    file_path = os.path.join(current_app.config['UPLOAD_FOLDER'], filename)
    if os.path.exists(file_path):
        try:
            os.remove(file_path)
        except OSError:
            current_app.logger.warning(f"Gagal menghapus file gambar lama: {file_path}")


@admin_bp.route('/dashboard')
@login_required
def dashboard():
    return render_template('admin/dashboard.html')


@admin_bp.route('/products')
@login_required
def product_list():
    products = query_db(
        'SELECT p.id, p.name, p.price, p.stock, p.image, p.is_active, c.name AS category_name '
        'FROM products p '
        'LEFT JOIN categories c ON p.category_id = c.id '
        'ORDER BY p.is_active DESC, p.created_at DESC'
    )
    return render_template('admin/products/list.html', products=products)


@admin_bp.route('/products/create', methods=['GET', 'POST'])
@login_required
def create_product():
    categories = query_db('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC')

    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        category_id = request.form.get('category_id', '').strip()
        description = request.form.get('description', '').strip()
        price = request.form.get('price', '').strip()
        stock = request.form.get('stock', '').strip()
        image_file = request.files.get('image')

        if not name or not category_id or not price or not stock:
            flash('Nama, kategori, harga, dan stok wajib diisi.', 'danger')
            return render_template('admin/products/create.html', categories=categories)

        if not image_file or image_file.filename == '':
            flash('Gambar produk wajib diunggah.', 'danger')
            return render_template('admin/products/create.html', categories=categories)

        if not allowed_file(image_file.filename):
            flash('Format gambar tidak valid. Hanya JPG dan PNG yang diperbolehkan.', 'danger')
            return render_template('admin/products/create.html', categories=categories)

        try:
            price_value = int(float(price))
            stock_value = int(stock)
            category_value = int(category_id)
        except ValueError:
            flash('Harga dan stok harus berupa angka yang valid.', 'danger')
            return render_template('admin/products/create.html', categories=categories)

        image_name = save_image_file(image_file)
        product_slug = slugify(name)

        insert_db(
            'INSERT INTO products (category_id, name, slug, description, price, stock, image) '
            'VALUES (%s, %s, %s, %s, %s, %s, %s)',
            (category_value, name, product_slug, description, price_value, stock_value, image_name)
        )

        flash('Produk berhasil ditambahkan.', 'success')
        return redirect(url_for('admin.product_list'))

    return render_template('admin/products/create.html', categories=categories)


@admin_bp.route('/products/edit/<int:product_id>', methods=['GET', 'POST'])
@login_required
def edit_product(product_id):
    product = query_db('SELECT * FROM products WHERE id = %s', (product_id,), one=True)
    if not product:
        flash('Produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.product_list'))

    categories = query_db('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC')

    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        category_id = request.form.get('category_id', '').strip()
        description = request.form.get('description', '').strip()
        price = request.form.get('price', '').strip()
        stock = request.form.get('stock', '').strip()
        image_file = request.files.get('image')

        if not name or not category_id or not price or not stock:
            flash('Nama, kategori, harga, dan stok wajib diisi.', 'danger')
            return render_template('admin/products/edit.html', product=product, categories=categories)

        try:
            price_value = int(float(price))
            stock_value = int(stock)
            category_value = int(category_id)
        except ValueError:
            flash('Harga dan stok harus berupa angka yang valid.', 'danger')
            return render_template('admin/products/edit.html', product=product, categories=categories)

        image_name = product.get('image')
        if image_file and image_file.filename:
            if not allowed_file(image_file.filename):
                flash('Format gambar tidak valid. Hanya JPG dan PNG yang diperbolehkan.', 'danger')
                return render_template('admin/products/edit.html', product=product, categories=categories)
            new_image_name = save_image_file(image_file)
            delete_image_file(product.get('image'))
            image_name = new_image_name

        product_slug = slugify(name)
        update_db(
            'UPDATE products SET category_id = %s, name = %s, slug = %s, description = %s, price = %s, stock = %s, image = %s WHERE id = %s',
            (category_value, name, product_slug, description, price_value, stock_value, image_name, product_id)
        )

        flash('Produk berhasil diperbarui.', 'success')
        return redirect(url_for('admin.product_list'))

    return render_template('admin/products/edit.html', product=product, categories=categories)


@admin_bp.route('/products/deactivate', methods=['POST'])
@login_required
def delete_product():
    product_id = request.form.get('product_id')
    if not product_id:
        flash('ID produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.product_list'))

    product = query_db('SELECT id, name, is_active FROM products WHERE id = %s', (product_id,), one=True)
    if not product:
        flash('Produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.product_list'))

    if not product['is_active']:
        flash('Produk sudah dalam kondisi nonaktif.', 'warning')
        return redirect(url_for('admin.product_list'))

    update_db('UPDATE products SET is_active = 0 WHERE id = %s', (product_id,))
    flash(f'Produk "{product["name"]}" berhasil dinonaktifkan.', 'success')
    return redirect(url_for('admin.product_list'))


@admin_bp.route('/products/activate', methods=['POST'])
@login_required
def activate_product():
    product_id = request.form.get('product_id')
    if not product_id:
        flash('ID produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.product_list'))

    product = query_db('SELECT id, name FROM products WHERE id = %s', (product_id,), one=True)
    if not product:
        flash('Produk tidak ditemukan.', 'danger')
        return redirect(url_for('admin.product_list'))

    update_db('UPDATE products SET is_active = 1 WHERE id = %s', (product_id,))
    flash(f'Produk "{product["name"]}" berhasil diaktifkan kembali.', 'success')
    return redirect(url_for('admin.product_list'))
