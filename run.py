#!/usr/bin/env python3
"""
run.py — Entry Point Aplikasi Toko Rini

Jalankan server Flask dengan: python run.py
"""
import sys
import os

# Tambahkan direktori root ke Python path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import create_app


if __name__ == '__main__':
    # Buat Flask app menggunakan factory pattern
    app = create_app()
    
    # Jalankan development server
    # Untuk production, gunakan gunicorn atau WSGI server lainnya
    port = int(os.getenv('FLASK_PORT', 5000))
    debug = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    
    print(f"\n{'='*60}")
    print(f"🏪 Toko Rini - Aplikasi Web Kelontong")
    print(f"{'='*60}")
    print(f"Server running on: http://localhost:{port}")
    print(f"Debug mode: {debug}")
    print(f"Press CTRL+C to stop the server")
    print(f"{'='*60}\n")
    
    app.run(
        host='0.0.0.0',
        port=port,
        debug=debug,
        use_reloader=debug
    )
