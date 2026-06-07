# 📦 Setup & Installation Guide - Toko Rini

Panduan lengkap untuk setup project Toko Rini (kelontong online) berbasis Flask + MySQL tanpa ORM.

## Prasyarat

- **Python 3.8+** (Rekomendasi: Python 3.10+)
- **MySQL Server 5.7+** (atau MariaDB 10.3+)
- **pip** (Python package manager)
- **git** (opsional, untuk version control)

---

## 1️⃣ Setup Database MySQL

### A. Buat Database dari Schema SQL

```bash
# Login ke MySQL
mysql -u root -p

# Dari dalam MySQL console, jalankan script schema:
source database/schema.sql;

# Atau jalankan dari command line:
mysql -u root -p < database/schema.sql
```

### B. Load Data Seed (Data Awal)

```bash
mysql -u root -p toko_rini < database/seed.sql
```

### C. Verifikasi Database

```bash
mysql -u root -p -e "USE toko_rini; SHOW TABLES; SELECT COUNT(*) as total_products FROM products;"
```

---

## 2️⃣ Setup Python Environment

### A. Create Virtual Environment

```bash
# Di direktori root project:
python3 -m venv venv

# Aktivasi virtual environment:
# Linux/Mac:
source venv/bin/activate

# Windows:
venv\Scripts\activate
```

### B. Install Dependencies

```bash
pip install -r requirements.txt
```

### C. Verifikasi Installation

```bash
python3 -c "import flask; import mysql; import bcrypt; print('✓ All dependencies installed')"
```

---

## 3️⃣ Konfigurasi Environment

### A. Buat File `.env`

```bash
cp .env.example .env
```

### B. Edit File `.env`

```ini
FLASK_ENV=development
FLASK_DEBUG=True
FLASK_PORT=5000
SECRET_KEY=your-secret-key-here

DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_NAME=toko_rini

MAX_CONTENT_LENGTH=2097152
```

---

## 4️⃣ Jalankan Aplikasi

### A. Start Flask Development Server

```bash
python run.py
```

Output akan terlihat:

```
============================================================
🏪 Toko Rini - Aplikasi Web Kelontong
============================================================
Server running on: http://localhost:5000
Debug mode: True
Press CTRL+C to stop the server
============================================================
```

### B. Test API Endpoints

```bash
# Di terminal baru:

# 1. Home endpoint
curl http://localhost:5000/

# 2. List categories
curl http://localhost:5000/api/categories

# 3. List products
curl http://localhost:5000/api/products

# 4. Admin products
curl http://localhost:5000/admin/products

# 5. Admin orders
curl http://localhost:5000/admin/orders
```

---

## 5️⃣ Admin Account

**Default Admin Credentials:**

- **Username:** `admin`
- **Password:** `admin123`
- **Full Name:** Rini Kusumawati

Password disimpan dalam bentuk **bcrypt hash** di database untuk keamanan.

---

## 6️⃣ Database Schema Summary

| Tabel | Deskripsi | Jumlah Data |
|-------|-----------|------------|
| `admins` | Akun pengelola toko | 1 |
| `categories` | Kategori produk kelontong | 5 |
| `products` | Produk yang dijual | 15+ |
| `orders` | Pesanan pelanggan | 3 (sample) |
| `order_items` | Detail item dalam order | 11 (sample) |

**Foreign Keys:**
- `products.category_id` → `categories.id`
- `order_items.order_id` → `orders.id`
- `order_items.product_id` → `products.id`

---

## 7️⃣ Struktur Project

```
membangun-web-umkm/
├── app/
│   ├── __init__.py           # Flask factory
│   ├── db.py                 # Database helpers (Raw SQL)
│   ├── routes/
│   │   └── __init__.py       # Blueprint initialization
│   ├── templates/            # HTML templates
│   └── static/               # CSS, JS, images
│       └── uploads/          # Upload folder untuk gambar
├── database/
│   ├── schema.sql            # Database schema (5 tabel)
│   └── seed.sql              # Data initial
├── run.py                    # Entry point
├── requirements.txt          # Python dependencies
├── .env.example              # Environment variables template
├── .gitignore                # Git ignore rules
├── SETUP.md                  # Setup guide (file ini)
└── README.md                 # Project README
```

---

## 8️⃣ Development Tips

### A. Database Connection Troubleshooting

Jika error connection:

```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify credentials
mysql -u root -p -h localhost

# Check if database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'toko_rini';"
```

### B. Debugging Flask App

Set `FLASK_DEBUG=True` di `.env` untuk auto-reload dan error page yang detail.

### C. Membuat Hashed Password

Untuk membuat bcrypt hash password sendiri:

```bash
python3 << EOF
import bcrypt
password = b'your_password_here'
hashed = bcrypt.hashpw(password, bcrypt.gensalt())
print(hashed.decode())
EOF
```

### D. Testing Database Query

Jalankan raw SQL via Python REPL:

```bash
python3 << EOF
from app import create_app
from app.db import query_db

app = create_app()
with app.app_context():
    products = query_db('SELECT * FROM products LIMIT 5')
    print(products)
EOF
```

---

## 9️⃣ Production Deployment

Untuk production, gunakan WSGI server seperti **Gunicorn**:

```bash
# Install gunicorn
pip install gunicorn

# Run with gunicorn (4 workers, production)
gunicorn -w 4 -b 0.0.0.0:5000 'app:create_app()' --timeout 120
```

---

## 🐳 Docker (Optional)

Anda dapat menjalankan aplikasi beserta MySQL menggunakan Docker dan Docker Compose. File `docker-compose.yml` sudah disertakan dan akan menginisialisasi database dari `database/schema.sql` dan `database/seed.sql`.

Langkah singkat:

```bash
# 1. (Opsional) edit `.env` untuk menyesuaikan kredensial database
cp .env.example .env
vi .env

# 2. Build dan jalankan container
docker compose up --build -d

# 3. Periksa logs
docker compose logs -f web

# 4. Stop dan hapus
docker compose down -v
```

Catatan:
- Direktori `./database` dipasang ke container MySQL di `/docker-entrypoint-initdb.d` sehingga `schema.sql` dan `seed.sql` dieksekusi otomatis saat database pertama kali dibuat.
- Pastikan port `3306` (MySQL) dan `5000` (Flask) tidak digunakan oleh proses lokal lain saat menjalankan docker-compose.


---

## ✅ Checklist Setup Berhasil

- [ ] MySQL server running
- [ ] Database `toko_rini` created
- [ ] Tables tercipta dengan FK correct
- [ ] Seed data loaded (categories, products, orders)
- [ ] Python virtual environment aktif
- [ ] Dependencies installed (`pip list`)
- [ ] `.env` file configured dengan DB credentials
- [ ] `python run.py` berjalan tanpa error
- [ ] `curl http://localhost:5000/` return 200
- [ ] API endpoints respond correctly

---

## 🆘 Troubleshooting

### Error: "No module named 'mysql'"

```bash
pip install mysql-connector-python
```

### Error: "Can't connect to MySQL server"

```bash
# 1. Check MySQL running
mysql -u root -p

# 2. Verify .env credentials
cat .env | grep DB_

# 3. Check charset in schema.sql
mysql -u root -p toko_rini -e "SHOW CREATE TABLE products\G"
```

### Error: "bcrypt.exceptions.InvalidHash"

Password hash di database tidak valid. Update dengan hash baru:

```python
import bcrypt
password = b'admin123'
hashed = bcrypt.hashpw(password, bcrypt.gensalt()).decode()
print(hashed)
```

---

## 📞 Support

Untuk pertanyaan atau issue, silakan buat issue di repository atau hubungi developer.

Happy coding! 🚀

---

**Last Updated:** 2025-06-01  
**Status:** ✅ Ready for Development
