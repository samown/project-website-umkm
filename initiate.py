from app import app, db
with app.app_context():
    db.create_all()
# lupa, mungking gk penting, cari tau ini ngapain, aku jalanin pas, sekitar koneksi db issue 