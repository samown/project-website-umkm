from flask import Blueprint, render_template, request, redirect, url_for, flash, session
from app.db import query_db
import bcrypt

auth_bp = Blueprint('auth', __name__, url_prefix='/admin')


@auth_bp.route('/login', methods=['GET'])
def admin_login():
    return render_template('auth/login.html')


@auth_bp.route('/login', methods=['POST'])
def admin_login_post():
    username = request.form.get('username', '').strip()
    password = request.form.get('password', '')

    if not username or not password:
        flash('Username dan password wajib diisi.', 'error')
        return redirect(url_for('auth.admin_login'))

    # Ambil hashed password dari database menggunakan parameterized query
    row = query_db('SELECT password FROM admins WHERE username = %s', (username,), one=True)

    if not row:
        flash('Username atau password salah.', 'error')
        return redirect(url_for('auth.admin_login'))

    stored_hash = row.get('password')
    try:
        # bcrypt expects bytes
        password_bytes = password.encode('utf-8')
        stored_hash_bytes = stored_hash.encode('utf-8')

        if bcrypt.checkpw(password_bytes, stored_hash_bytes):
            session['admin_logged_in'] = True
            session['admin_username'] = username
            return redirect(url_for('admin.dashboard'))
        else:
            flash('Username atau password salah.', 'error')
            return redirect(url_for('auth.admin_login'))

    except Exception:
        flash('Terjadi kesalahan saat memverifikasi password.', 'error')
        return redirect(url_for('auth.admin_login'))


@auth_bp.route('/logout')
def admin_logout():
    session_keys = [k for k in list(session.keys()) if k.startswith('admin_')]
    for k in session_keys:
        session.pop(k, None)
    session.pop('admin_logged_in', None)
    return redirect(url_for('auth.admin_login'))
