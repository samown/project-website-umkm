from functools import wraps
from flask import session, redirect, url_for


def login_required(view_func):
    """
    Decorator untuk memastikan user admin sudah login.
    Mengecek `session['admin_logged_in']` dan mengarahkan ke halaman login jika belum.
    """
    @wraps(view_func)
    def wrapped_view(*args, **kwargs):
        if not session.get('admin_logged_in'):
            return redirect(url_for('auth.admin_login'))
        return view_func(*args, **kwargs)

    return wrapped_view
