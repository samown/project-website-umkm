"""
app/db.py — Database Helper (Raw SQL, tanpa ORM)
Semua query ditulis manual menggunakan mysql-connector-python.
Menyediakan fungsi get_db() yang mengembalikan koneksi per-request Flask.
"""
import mysql.connector
from flask import g, current_app


def get_db():
    """
    Mengembalikan koneksi MySQL yang tersimpan di Flask g (request context).
    Koneksi dibuat sekali per request dan ditutup otomatis saat request selesai.
    """
    if 'db' not in g:
        g.db = mysql.connector.connect(
            host     = current_app.config['DB_HOST'],
            port     = current_app.config['DB_PORT'],
            user     = current_app.config['DB_USER'],
            password = current_app.config['DB_PASSWORD'],
            database = current_app.config['DB_NAME'],
            charset  = 'utf8mb4',
        )
    return g.db


def close_db(e=None):
    """Tutup koneksi DB saat request selesai (dipanggil via teardown_appcontext)."""
    db = g.pop('db', None)
    if db is not None and db.is_connected():
        db.close()


def query_db(sql, args=(), one=False, commit=False):
    """
    Utilitas eksekusi query.
    - sql    : string query dengan placeholder %s
    - args   : tuple parameter (aman dari SQL injection)
    - one    : True → kembalikan 1 baris; False → semua baris
    - commit : True → eksekusi INSERT/UPDATE/DELETE lalu commit
    Mengembalikan:
      - SELECT  → list[dict] atau dict atau None
      - DML     → lastrowid (int)
    """
    conn   = get_db()
    cursor = conn.cursor(dictionary=True)  # hasil berupa dict
    cursor.execute(sql, args)

    if commit:
        conn.commit()
        last_id = cursor.lastrowid
        cursor.close()
        return last_id

    result = cursor.fetchone() if one else cursor.fetchall()
    cursor.close()
    return result
