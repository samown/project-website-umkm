"""
app/db.py — Database Helper dengan Raw SQL

Menyediakan fungsi-fungsi untuk berinteraksi dengan MySQL database.
Menggunakan mysql-connector-python (tanpa ORM).

Prinsip:
- Setiap request Flask mendapat koneksi MySQL baru (disimpan di Flask g object)
- Koneksi ditutup otomatis saat request selesai (teardown_appcontext)
- Query ditulis langsung dengan SQL, aman dari SQL injection (parameterized query)
"""
import mysql.connector
from mysql.connector import Error
from flask import g, current_app


def get_db():
    """
    Mengembalikan koneksi MySQL untuk request saat ini.
    
    Koneksi disimpan di Flask g (request context) dan reuse jika sudah ada.
    Koneksi dibuat sekali per request dan ditutup otomatis saat request selesai.
    
    Returns:
        mysql.connector.MySQLConnection: Koneksi ke database MySQL
    """
    if 'db' not in g:
        try:
            g.db = mysql.connector.connect(
                host=current_app.config['DB_HOST'],
                port=current_app.config['DB_PORT'],
                user=current_app.config['DB_USER'],
                password=current_app.config['DB_PASSWORD'],
                database=current_app.config['DB_NAME'],
                charset=current_app.config['DB_CHARSET'],
                autocommit=False  # Manual commit untuk kontrol transaksi
            )
        except Error as e:
            current_app.logger.error(f"Database connection failed: {e}")
            raise
    
    return g.db


def close_db(e=None):
    """
    Menutup koneksi database saat request selesai.
    
    Dipanggil otomatis oleh Flask via app.teardown_appcontext.
    
    Args:
        e: Exception (jika ada) yang terjadi saat request
    """
    db = g.pop('db', None)
    if db is not None and db.is_connected():
        try:
            db.close()
        except Error as e:
            current_app.logger.error(f"Error closing database: {e}")


def query_db(sql, args=(), one=False, commit=False, fetch_all=True):
    """
    Utilitas untuk eksekusi query SQL dengan error handling.
    
    Parameters:
        sql (str): Query SQL dengan placeholder %s (parameterized query)
        args (tuple): Tuple parameter untuk query (aman dari SQL injection)
        one (bool): True → kembalikan 1 baris; False → semua baris
        commit (bool): True → auto-commit setelah INSERT/UPDATE/DELETE
        fetch_all (bool): True → fetch semua hasil; False → return cursor
    
    Returns:
        - SELECT queries:
            - one=True: dict (single row) atau None
            - one=False: list[dict] (all rows)
        - INSERT/UPDATE/DELETE queries:
            - lastrowid (int) untuk INSERT
            - rowcount (int) untuk UPDATE/DELETE
    
    Raises:
        mysql.connector.Error: Jika ada error SQL
    """
    conn = get_db()
    cursor = conn.cursor(dictionary=True, buffered=True)
    
    try:
        cursor.execute(sql, args)
        
        if commit:
            conn.commit()
        
        # Untuk SELECT query
        if sql.strip().upper().startswith('SELECT'):
            if fetch_all:
                if one:
                    result = cursor.fetchone()
                    cursor.close()
                    return result
                else:
                    results = cursor.fetchall()
                    cursor.close()
                    return results
            else:
                return cursor
        
        # Untuk INSERT query
        elif sql.strip().upper().startswith('INSERT'):
            cursor.close()
            return cursor.lastrowid
        
        # Untuk UPDATE/DELETE query
        else:
            row_count = cursor.rowcount
            cursor.close()
            return row_count
    
    except Error as e:
        if commit:
            conn.rollback()
        cursor.close()
        current_app.logger.error(f"Database query error: {e}")
        raise


def insert_db(sql, args=(), multiple=False):
    """
    Helper khusus untuk INSERT query.
    
    Parameters:
        sql (str): INSERT query dengan placeholder %s
        args (tuple/list): Single tuple atau list of tuples untuk multiple insert
        multiple (bool): True → executemany; False → execute single
    
    Returns:
        int: lastrowid untuk single insert, atau rowcount untuk multiple insert
    """
    conn = get_db()
    cursor = conn.cursor()
    
    try:
        if multiple:
            cursor.executemany(sql, args)
            row_count = cursor.rowcount
        else:
            cursor.execute(sql, args)
            row_count = cursor.lastrowid
        
        conn.commit()
        cursor.close()
        return row_count
    
    except Error as e:
        conn.rollback()
        cursor.close()
        current_app.logger.error(f"Insert query error: {e}")
        raise


def update_db(sql, args=()):
    """
    Helper khusus untuk UPDATE query.
    
    Parameters:
        sql (str): UPDATE query dengan placeholder %s
        args (tuple): Parameter untuk query
    
    Returns:
        int: Jumlah baris yang terpengaruh (rowcount)
    """
    conn = get_db()
    cursor = conn.cursor()
    
    try:
        cursor.execute(sql, args)
        row_count = cursor.rowcount
        conn.commit()
        cursor.close()
        return row_count
    
    except Error as e:
        conn.rollback()
        cursor.close()
        current_app.logger.error(f"Update query error: {e}")
        raise


def delete_db(sql, args=()):
    """
    Helper khusus untuk DELETE query.
    
    Parameters:
        sql (str): DELETE query dengan placeholder %s
        args (tuple): Parameter untuk query
    
    Returns:
        int: Jumlah baris yang terhapus (rowcount)
    """
    conn = get_db()
    cursor = conn.cursor()
    
    try:
        cursor.execute(sql, args)
        row_count = cursor.rowcount
        conn.commit()
        cursor.close()
        return row_count
    
    except Error as e:
        conn.rollback()
        cursor.close()
        current_app.logger.error(f"Delete query error: {e}")
        raise


def call_procedure(procedure_name, args=()):
    """
    Memanggil stored procedure MySQL.
    
    Parameters:
        procedure_name (str): Nama procedure
        args (tuple): Parameter untuk procedure
    
    Returns:
        list[dict]: Hasil dari procedure
    """
    conn = get_db()
    cursor = conn.cursor(dictionary=True)
    
    try:
        cursor.callproc(procedure_name, args)
        results = []
        
        for result in cursor.fetchall():
            results.append(result)
        
        conn.commit()
        cursor.close()
        return results
    
    except Error as e:
        conn.rollback()
        cursor.close()
        current_app.logger.error(f"Procedure call error: {e}")
        raise
