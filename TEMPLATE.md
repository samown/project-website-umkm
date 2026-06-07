## 🏪 Template Project: Toko Rini

Folder root ini sudah berisi **kerangka project template** bernama **"Toko Rini"** — sebuah aplikasi web kelontong online yang siap untuk dikembangkan lebih lanjut.

### Deskripsi Template

**Toko Rini** adalah template aplikasi e-commerce sederhana untuk toko kelontong yang mengimplementasikan:

- ✅ **Backend:** Python Flask (tanpa ORM)
- ✅ **Database:** MySQL dengan 5 tabel berelasi + Foreign Keys
- ✅ **Data Awal:** Schema, seed data (admin, 5 kategori, 15+ produk, sample orders)
- ✅ **Admin Authentication:** Login dengan bcrypt password hashing
- ✅ **API Endpoints:** Public API untuk kategori, produk; Admin dashboard endpoints
- ✅ **Raw SQL:** Semua query ditulis langsung (tanpa SQLAlchemy/ORM)
- ✅ **Environment Configuration:** File `.env` untuk konfigurasi fleksibel

### Struktur Template

```
membangun-web-umkm/
├── app/
│   ├── __init__.py           # Flask factory & konfigurasi
│   ├── db.py                 # Database helpers (raw SQL)
│   ├── routes/
│   │   └── __init__.py       # Blueprint initialization
│   ├── templates/            # HTML templates (kosong, siap diisi)
│   └── static/               # CSS, JS, images
├── database/
│   ├── schema.sql            # 5 tabel: admins, categories, products, orders, order_items
│   └── seed.sql              # Data awal: admin (bcrypt hash), kategori, produk
├── run.py                    # Entry point Flask server
├── requirements.txt          # Dependencies: Flask, mysql-connector-python, bcrypt
├── .env.example              # Template environment variables
├── .env                      # Environment variables (gitignore)
├── .gitignore                # Git ignore rules
├── SETUP.md                  # Panduan setup & running project
└── README.md                 # File ini
```

### Quick Start Template

```bash
# 1. Setup Database
mysql -u root -p < database/schema.sql
mysql -u root -p toko_rini < database/seed.sql

# 2. Setup Python Environment
python3 -m venv venv
source venv/bin/activate

# 3. Install Dependencies
pip install -r requirements.txt

# 4. Konfigurasi .env (jika perlu)
# Edit .env dengan credentials MySQL Anda

# 5. Jalankan Server
python run.py

# 6. Test API
curl http://localhost:5000/api/products
curl http://localhost:5000/admin/orders
```

### Admin Credentials (Default)

- **Username:** `admin`
- **Password:** `admin123`

Password disimpan sebagai bcrypt hash di database untuk keamanan.

### Fitur yang Sudah Implementasikan

- [x] Database schema dengan 5 tabel + Foreign Keys
- [x] Seed data: admin (bcrypt), 5 kategori, 15+ produk, 3 sample orders
- [x] Flask app factory pattern
- [x] Database layer dengan raw SQL (PyMySQL/mysql-connector-python)
- [x] Blueprint-based routing (public, auth, admin)
- [x] Environment configuration (.env)
- [x] Basic API endpoints
- [x] Admin authentication structure

### Fitur yang Perlu Dikembangkan

Untuk menyelesaikan tugas sesuai spesifikasi, tambahkan:

- [ ] Frontend HTML/CSS (halaman publik, login, admin dashboard)
- [ ] Login form & session management
- [ ] CRUD operations untuk produk & kategori (admin panel)
- [ ] File upload untuk gambar produk
- [ ] Shopping cart & order creation
- [ ] WhatsApp contact button
- [ ] Halaman beranda dengan info toko & peta lokasi
- [ ] Form kontak
- [ ] Responsive UI (Bootstrap/Tailwind)
- [ ] Validasi input & error handling
- [ ] Testing & dokumentasi lengkap

### Dokumentasi Lengkap

Lihat file **[SETUP.md](SETUP.md)** untuk:
- Panduan setup database
- Troubleshooting
- Database connection testing
- Production deployment tips

---

## 💡 Menggunakan Template Ini untuk Tugas Anda

Anda dapat menggunakan template Toko Rini ini sebagai fondasi untuk project UMKM/PCM Anda:

1. **Ganti data domain** (Toko Rini → UMKM/PCM Anda)
2. **Sesuaikan tabel database** dengan kebutuhan spesifik (tambah tabel untuk member, program, invoice, dll)
3. **Kembangkan frontend** sesuai fitur yang diminta
4. **Implementasikan semua fitur wajib** dari spesifikasi tugas
5. **Update README** dengan informasi project Anda

Template ini sudah mengikuti semua konvensi wajib:
- ✅ Raw SQL (tanpa ORM)
- ✅ Password hashing (bcrypt)
- ✅ Database dengan relasi & Foreign Keys
- ✅ Environment configuration
- ✅ Admin authentication
- ✅ Setup guide yang jelas

---

**Template Last Updated:** 2025-06-01  
**Template Status:** ✅ Ready for Development