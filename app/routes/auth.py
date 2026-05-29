"""
app/routes/auth.py — Autentikasi Admin
Menangani /login dan /logout.
Password diverifikasi dengan bcrypt (tidak pernah disimpan plaintext).
Sesi dikelola via Flask session (server-side signed cookie).
"""
import bcrypt
from flask import (Blueprint, render_template, request,
                   redirect, url_for, session, flash)
from app.db import query_db

auth_bp = Blueprint('auth', __name__)


@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    # Jika sudah login, langsung ke dashboard
    if session.get('admin_id'):
        return redirect(url_for('admin.dashboard'))

    error = None
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '').encode('utf-8')

        # Raw SQL — ambil admin berdasarkan username
        admin = query_db(
            "SELECT id, username, password, full_name FROM admins WHERE username = %s",
            (username,),
            one=True
        )

        # Verifikasi password dengan bcrypt
        if admin and bcrypt.checkpw(password, admin['password'].encode('utf-8')):
            session.clear()
            session['admin_id']       = admin['id']
            session['admin_username'] = admin['username']
            session['admin_name']     = admin['full_name']
            return redirect(url_for('admin.dashboard'))
        else:
            error = 'Username atau password salah.'

    return render_template('login.html', error=error)


@auth_bp.route('/logout')
def logout():
    session.clear()
    flash('Anda telah keluar dari panel admin.', 'info')
    return redirect(url_for('auth.login'))
