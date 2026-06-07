"""
app/__init__.py — Flask Application Factory

Pola Factory mempermudah konfigurasi, testing, dan deployment.
Menggunakan environment variables untuk konfigurasi yang fleksibel.

Tanpa ORM — Semua database interaction menggunakan Raw SQL dengan mysql-connector-python.
"""
import os
from flask import Flask, g
from dotenv import load_dotenv

# Muat variabel environment dari file .env
load_dotenv()


def create_app():
    """
    Factory function untuk membuat dan mengonfigurasi Flask app.
    """
    app = Flask(
        __name__,
        template_folder='templates',
        static_folder='static',
        static_url_path='/static'
    )

    # ================================================================
    # Konfigurasi dari environment variables
    # ================================================================
    app.config['SECRET_KEY'] = os.getenv(
        'SECRET_KEY',
        'dev-secret-key-ganti-di-produksi-production'
    )
    app.config['DEBUG'] = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    app.config['TESTING'] = False
    app.config['MAX_CONTENT_LENGTH'] = int(
        os.getenv('MAX_CONTENT_LENGTH', 2 * 1024 * 1024)  # 2 MB default
    )

    # ================================================================
    # Konfigurasi Database MySQL (Raw SQL, Tanpa ORM)
    # ================================================================
    app.config['DB_HOST'] = os.getenv('DB_HOST', 'localhost')
    app.config['DB_PORT'] = int(os.getenv('DB_PORT', 3306))
    app.config['DB_USER'] = os.getenv('DB_USER', 'root')
    app.config['DB_PASSWORD'] = os.getenv('DB_PASSWORD', '')
    app.config['DB_NAME'] = os.getenv('DB_NAME', 'toko_rini')
    app.config['DB_CHARSET'] = 'utf8mb4'

    # ================================================================
    # Konfigurasi Upload Folder untuk Gambar Produk
    # ================================================================
    app.config['UPLOAD_FOLDER'] = os.path.join(app.static_folder, 'uploads')
    app.config['ALLOWED_EXTENSIONS'] = {'png', 'jpg', 'jpeg'}
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

    # ================================================================
    # Inisialisasi Database Connection & Cleanup
    # ================================================================
    from app.db import get_db, close_db

    # Registrasi hook untuk menutup koneksi database saat request selesai
    app.teardown_appcontext(close_db)

    # ================================================================
    # Daftarkan Blueprint untuk Modular Routing
    # ================================================================
    from app.routes import init_routes
    init_routes(app)

    # ================================================================
    # Error Handler
    # ================================================================
    @app.errorhandler(404)
    def not_found(error):
        return {'error': 'Route tidak ditemukan'}, 404

    @app.errorhandler(500)
    def internal_error(error):
        return {'error': 'Internal server error'}, 500

    return app
