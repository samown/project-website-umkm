# 🛒 Toko Rini — Aplikasi Web UMKM Kelontong

Aplikasi website untuk UMKM toko kelontong **Toko Rini** yang berlokasi di Banjarsari, Surakarta.
Dibangun dengan **Flask + MySQL (Raw SQL)** dan antarmuka **Tailwind CSS + Alpine.js**.

---

## 👥 Anggota Kelompok

| No | Nama | NIM | Kontribusi |
|----|------|-----|-----------|
| 1  | (isi) | (isi) | Backend & Database |
| 2  | (isi) | (isi) | Frontend Publik |
| 3  | (isi) | (isi) | Panel Admin |
| 4  | (isi) | (isi) | Keranjang & Checkout |
| 5  | (isi) | (isi) | Testing & Dokumentasi |

---

## 🛠️ Stack Teknologi

| Layer | Teknologi |
|-------|-----------|
| Frontend | HTML, Tailwind CSS (CDN), Alpine.js |
| Backend | Python 3.10+, Flask 3.1 |
| Database | MySQL 8+ |
| Auth | bcrypt, Flask Session |
| DB Driver | mysql-connector-python (Raw SQL, **tanpa ORM**) |

---

## 📁 Struktur Folder

```
toko-rini-app/
├── app/
│   ├── __init__.py          # Application factory
│   ├── db.py                # Helper koneksi & query Raw SQL
│   ├── routes/
│   │   ├── auth.py          # Login / logout
│   │   ├── admin.py         # Panel admin (CRUD produk, pesanan)
│   │   └── public.py        # Beranda, katalog, checkout
│   ├── templates/
│   │   ├── base.html        # Layout dasar publik
│   │   ├── index.html       # Beranda
│   │   ├── produk.html      # Katalog produk
│   │   ├── login.html       # Halaman login admin
│   │   └── admin/
│   │       ├── base_admin.html
│   │       ├── dashboard.html
│   │       ├── produk_list.html
│   │       ├── produk_form.html
│   │       ├── pesanan_list.html
│   │       └── pesanan_detail.html
│   └── static/
│       └── images/          # Gambar produk yang diupload
├── database/
│   ├── schema.sql           # DDL: CREATE TABLE + FK
│   └── seed.sql             # Data awal admin + produk
├── .env.example             # Template konfigurasi
├── .gitignore
├── requirements.txt
├── run.py
└── README.md
```

---

## ⚙️ Cara Menjalankan Aplikasi

### Prasyarat
- Python 3.10 atau lebih baru
- MySQL 8.0 atau lebih baru
- pip

### Langkah-langkah

```bash
# 1. Clone repositori
git clone <url-repo>
cd toko-rini-app

# 2. Buat virtual environment
python -m venv venv

# Windows
venv\Scripts\activate

# macOS / Linux
source venv/bin/activate

# 3. Install dependensi
pip install -r requirements.txt

# 4. Salin dan isi konfigurasi
cp .env.example .env
# Buka .env lalu isi:
#   DB_HOST, DB_USER, DB_PASSWORD, DB_NAME
#   SECRET_KEY (isi dengan string acak panjang)

# 5. Buat database dan jalankan schema
mysql -u root -p < database/schema.sql

# 6. Isi data awal (produk & akun admin)
mysql -u root -p toko_rini < database/seed.sql

# 7. Jalankan aplikasi
python run.py
```

Buka browser ke: **http://localhost:5000**

### Akun Admin Default

| Field | Nilai |
|-------|-------|
| URL Login | http://localhost:5000/login |
| Username | `admin` |
| Password | `admin123` |

> ⚠️ **Ganti password admin segera** setelah pertama kali login di lingkungan produksi.

---

## ✨ Fitur Aplikasi

### Halaman Publik
- **Beranda** — Profil Toko Rini, produk terbaru, peta lokasi (OpenStreetMap), tombol WhatsApp
- **Katalog Produk** — Filter kategori + pencarian nama (SQL `LIKE`), tampilan grid responsif
- **Keranjang Belanja** — Cart berbasis Alpine.js (client-side), floating cart button
- **Checkout via WhatsApp** — Data pesanan dikirim ke server → disimpan ke DB → redirect ke `wa.me`

### Panel Admin (dilindungi login)
- **Dashboard** — Statistik COUNT() dan SUM() dari database
- **CRUD Produk** — Tambah, edit, hapus produk + upload foto JPG/PNG
- **Manajemen Pesanan** — Lihat daftar, detail, dan update status pesanan

---

## 🗄️ Skema Database (5 Tabel Berelasi)

```
admins ──────────────────────────────────
  id, username, password (bcrypt), full_name

categories ──────────────────────────────
  id, name, slug

products ────────────────────────────────
  id, category_id (FK→categories), name,
  description, price, stock, image, is_active

orders ──────────────────────────────────
  id, customer_name, customer_phone,
  customer_address, total_price, status, notes

order_items ─────────────────────────────
  id, order_id (FK→orders), product_id (FK→products),
  product_name*, price*, quantity, subtotal
  (* = snapshot saat pesan, jaga histori)
```

Contoh query JOIN yang digunakan di kode:
```sql
-- Katalog dengan nama kategori
SELECT p.id, p.name, p.price, c.name AS category_name
FROM   products p
JOIN   categories c ON c.id = p.category_id
WHERE  p.is_active = 1 AND p.name LIKE '%beras%';

-- Detail pesanan dengan gambar produk
SELECT oi.product_name, oi.quantity, oi.subtotal, p.image
FROM   order_items oi
LEFT JOIN products p ON p.id = oi.product_id
WHERE  oi.order_id = 1;
```

---

## 🔒 Keamanan

- Password admin di-hash dengan `bcrypt` (cost factor 12)
- Semua query menggunakan **parameterised statements** (`%s`) → aman dari SQL Injection
- Semua route `/admin/*` dilindungi decorator `@login_required`
- Upload gambar divalidasi ekstensi (jpg, jpeg, png, webp) dan diberi nama acak UUID
- File `.env` tidak pernah di-commit (ada di `.gitignore`)

---

*Tugas Pemrograman Web — Universitas Muhammadiyah Surakarta*
