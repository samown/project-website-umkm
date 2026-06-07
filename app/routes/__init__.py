"""
app/routes/__init__.py — Routes Blueprint Initialization

Mendaftarkan semua blueprint untuk modular routing.
"""
from app.routes.public import public_bp
from app.routes.auth import auth_bp
from app.routes.admin import admin_bp


def init_routes(app):
    """Register blueprints from routes package."""
    app.register_blueprint(public_bp)
    app.register_blueprint(auth_bp)
    app.register_blueprint(admin_bp)
