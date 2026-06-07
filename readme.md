# Toko Rini — Website Toko Kelontong

Website toko kelontong online berbasis **Native PHP + MySQL + PDO**, lengkap dengan halaman publik dan panel admin.

---

## 🗂️ Struktur Folder

```
toko-rini/
├── public/                 # Document root server
│   ├── index.php           # Halaman utama (beranda)
│   ├── css/
│   │   ├── style.css       # Tema halaman publik
│   │   └── admin.css       # Tema panel admin
│   ├── js/
│   │   ├── main.js         # JS publik
│   │   └── admin.js        # JS admin
│   └── images/             # Gambar produk yang diupload
├── src/
│   ├── config/
│   │   └── database.php    # Koneksi PDO
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── logout.php
│   │   ├── admin/
│   │   │   ├── dashboard.php
│   │   │   ├── products.php
│   │   │   ├── product_form.php
│   │   │   ├── categories.php
│   │   │   ├── orders.php
│   │   │   └── order_detail.php
│   │   └── public/
│   │       ├── products.php
│   │       └── contact.php
│   └── includes/
│       ├── helpers.php
│       ├── header_public.php
│       ├── footer_public.php
│       ├── header_admin.php
│       └── footer_admin.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── .env.example
└── README.md
```

---

## ⚡ Cara Setup

### No bullshit

cp .env.example .env
di sql phpmyadmin CREATE DATABASE IF NOT EXISTS toko_rini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
import schema.sql => seed.sql
cp .env.example .env
php -S localhost:8000 -t public.
_________________________

### with bullshit

### 1. Clone & Konfigurasi .env

```bash
cp .env.example .env
```

Edit `.env` dan isi sesuai konfigurasi:

```env
DB_HOST=localhost
DB_NAME=toko_rini
DB_USER=root
DB_PASS=your_password

APP_NAME=Toko Rini
APP_URL=http://localhost/toko-rini

WHATSAPP_NUMBER=6285249296758
STORE_ADDRESS=Jl. Contoh No. 1, Surakarta, Jawa Tengah
MAPS_EMBED_URL=https://maps.google.com/maps?q=-7.5755,110.8243&z=15&output=embed
```

### 2. Buat Database & Import Schema

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS toko_rini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p toko_rini < database/schema.sql
mysql -u root -p toko_rini < database/seed.sql
```

### 3. Konfigurasi Web Server

**Apache** — pastikan Document Root mengarah ke folder `toko-rini/` (bukan `public/`), atau tambahkan Virtual Host:

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/toko-rini
    ServerName toko-rini.local
    <Directory /path/to/toko-rini>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**PHP Built-in Server (development):**

```bash
php -S localhost:8000 -t public.
```

Kemudian akses: `http://localhost:8000/public/index.php`

### 4. Buat Folder Images (jika belum ada)

```bash
mkdir -p public/images
chmod 755 public/images
```

---

## 🔐 Akun Admin Default

| Field    | Value    |
|----------|----------|
| Username | `admin`  |
| Password | `ipin101119` |

URL Login: `/src/pages/auth/login.php`

> ⚠️ **Segera ganti password** setelah login pertama kali!

---

## ✨ Fitur

### Halaman Publik
- **Beranda** — Profil toko, produk unggulan, kategori, peta Google Maps
- **Produk** — Grid produk dengan filter kategori & pencarian
- **Kontak** — Info kontak, jam operasional, tombol WhatsApp, peta

### Panel Admin
- **Dashboard** — Statistik ringkasan (produk, pesanan, pendapatan, stok rendah)
- **Manajemen Produk** — CRUD lengkap dengan upload gambar (JPG/PNG/WebP)
- **Manajemen Kategori** — CRUD kategori produk
- **Manajemen Pesanan** — Lihat daftar pesanan, ubah status, lihat detail

---

## 🔒 Keamanan

- Password di-hash dengan **bcrypt** (`password_hash` / `password_verify`)
- Semua query database menggunakan **PDO Prepared Statements** (aman dari SQL Injection)
- Semua output di-escape dengan `htmlspecialchars()` (aman dari XSS)
- Halaman admin dilindungi session check
- Upload gambar divalidasi MIME type, ekstensi, dan ukuran
- Session di-regenerate saat login (`session_regenerate_id`)

---

## 🛠️ Teknologi

- **Backend:** Native PHP 8.1+
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Koneksi DB:** PDO dengan prepared statements
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (tanpa framework)
- **Font:** Playfair Display + DM Sans (Google Fonts)
