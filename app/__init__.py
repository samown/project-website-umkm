"""
app/__init__.py — Flask Application Factory
Menggunakan pola factory agar konfigurasi mudah diubah dan testable.
"""
import os
from flask import Flask
from dotenv import load_dotenv

# Muat variabel dari file .env
load_dotenv()


def create_app():
    app = Flask(__name__, template_folder='templates', static_folder='static')

    # ----------------------------------------------------------------
    # Konfigurasi dari environment variables
    # ----------------------------------------------------------------
    app.config['SECRET_KEY']          = os.getenv('SECRET_KEY', 'dev-secret-key-ganti-di-produksi')
    app.config['DEBUG']               = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    app.config['MAX_CONTENT_LENGTH']  = int(os.getenv('MAX_CONTENT_LENGTH', 2 * 1024 * 1024))  # 2 MB

    # Konfigurasi MySQL — diakses oleh db.py
    app.config['DB_HOST']     = os.getenv('DB_HOST', 'localhost')
    app.config['DB_PORT']     = int(os.getenv('DB_PORT', 3306))
    app.config['DB_USER']     = os.getenv('DB_USER', 'root')
    app.config['DB_PASSWORD'] = os.getenv('DB_PASSWORD', '')
    app.config['DB_NAME']     = os.getenv('DB_NAME', 'toko_rini')

    # Folder upload gambar produk
    app.config['UPLOAD_FOLDER'] = os.path.join(app.static_folder, 'images')
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

    # ----------------------------------------------------------------
    # Daftarkan Blueprint (modular routing)
    # ----------------------------------------------------------------
    from app.routes.public import public_bp
    from app.routes.auth   import auth_bp
    from app.routes.admin  import admin_bp

    app.register_blueprint(public_bp)
    app.register_blueprint(auth_bp)
    app.register_blueprint(admin_bp, url_prefix='/admin')

    return app
