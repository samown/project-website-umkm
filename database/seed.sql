-- =============================================================
-- seed.sql — Data awal Toko Rini
-- Jalankan SETELAH schema.sql:
--   mysql -u root -p toko_rini < database/seed.sql
-- =============================================================

USE toko_rini;

-- ------------------------------------------------------------
-- Admin default
-- Username: admin | Password: admin123
-- Hash bcrypt digenerate via Python: bcrypt.hashpw(b'admin123', bcrypt.gensalt())
-- Untuk regenerate hash: python -c "import bcrypt; print(bcrypt.hashpw(b'admin123', bcrypt.gensalt()).decode())"
-- ------------------------------------------------------------
INSERT INTO admins (username, password, full_name) VALUES
(
    'admin',
    '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/HS6S.ji',
    'Rini Kusumawati'
);

-- ------------------------------------------------------------
-- Kategori produk kelontong
-- ------------------------------------------------------------
INSERT INTO categories (name, slug) VALUES
('Beras & Biji-bijian',  'beras-biji'),
('Minyak & Mentega',     'minyak-mentega'),
('Bumbu & Rempah',       'bumbu-rempah'),
('Minuman',              'minuman'),
('Snack & Camilan',      'snack-camilan'),
('Kebutuhan Dapur',      'kebutuhan-dapur'),
('Kebersihan',           'kebersihan');

-- ------------------------------------------------------------
-- Produk kelontong
-- ------------------------------------------------------------
INSERT INTO products (category_id, name, description, price, stock, image) VALUES
-- Beras & Biji-bijian (id=1)
(1, 'Beras Pandan Wangi 5 kg',     'Beras premium pulen wangi, cocok untuk nasi putih sehari-hari.',          65000,  50, NULL),
(1, 'Beras IR64 10 kg',             'Beras medium giling putih, ekonomis untuk keluarga.',                     95000,  40, NULL),
(1, 'Kacang Hijau 500 g',           'Kacang hijau pilihan, bagus untuk bubur dan minuman.',                    14000,  60, NULL),

-- Minyak & Mentega (id=2)
(2, 'Minyak Goreng Tropical 2 L',   'Minyak goreng sawit jernih, bebas kolesterol jahat.',                     32000,  80, NULL),
(2, 'Margarin Simas 200 g',         'Margarin serbaguna untuk masak dan mengoles roti.',                       12000, 100, NULL),

-- Bumbu & Rempah (id=3)
(3, 'Garam Refina 500 g',           'Garam beryodium halus untuk masak dan pengawet.',                          5000, 150, NULL),
(3, 'Gula Pasir Rose Brand 1 kg',   'Gula pasir putih halus, manis alami.',                                    14500,  90, NULL),
(3, 'Kecap Manis ABC 135 ml',       'Kecap manis pekat khas Indonesia, pas untuk semua masakan.',               8000, 120, NULL),
(3, 'Terasi Udang Indofood 10 g',   'Terasi udang pilihan, aromatis dan gurih.',                                3500, 200, NULL),

-- Minuman (id=4)
(4, 'Teh Celup Sariwangi 50 pcs',   'Teh hitam pilihan dalam kemasan celup praktis.',                          12000, 100, NULL),
(4, 'Kopi Kapal Api Spesial 165 g', 'Kopi bubuk robusta pilihan, aroma kuat dan nikmat.',                      18000,  75, NULL),
(4, 'Sirup Marjan Cocopandan 460 ml','Sirup segar rasa cocopandan untuk minuman dingin.',                       24000,  50, NULL),

-- Snack & Camilan (id=5)
(5, 'Biskuit Marie Regal 300 g',    'Biskuit renyah klasik Indonesia, enak dimakan langsung atau dicelup.',     14000,  80, NULL),
(5, 'Keripik Singkong Pedas 200 g', 'Keripik singkong gurih dan pedas, camilan favorit keluarga.',              12000,  60, NULL),
(5, 'Wafer Tango Cokelat 130 g',    'Wafer cokelat berlapis krim lezat.',                                       10000,  90, NULL),

-- Kebutuhan Dapur (id=6)
(6, 'Sabun Cuci Sunlight 755 ml',   'Sabun cuci piring anti lemak dan beraroma jeruk segar.',                   18000,  70, NULL),
(6, 'Plastik Bungkus Kiloan 1 kg',  'Plastik bungkus tebal serbaguna ukuran 30x40 cm.',                        15000,  40, NULL),

-- Kebersihan (id=7)
(7, 'Sabun Mandi Lifebuoy 80 g',    'Sabun antiseptik perlindungan kuman 10x lebih baik.',                       4500, 200, NULL),
(7, 'Deterjen Rinso 800 g',         'Deterjen bubuk dengan formula power clean untuk noda membandel.',          22000,  60, NULL),
(7, 'Shampoo Pantene 170 ml',       'Sampo anti rambut rontok dengan formula Pro-V.',                           28000,  45, NULL);

-- ------------------------------------------------------------
-- Contoh order (opsional, untuk demo dashboard)
-- ------------------------------------------------------------
INSERT INTO orders (customer_name, customer_phone, customer_address, total_price, status, notes) VALUES
('Budi Santoso',   '08123456789', 'Jl. Kenanga No. 5, Surakarta', 111000, 'completed', 'Minta kantong plastik'),
('Siti Rahayu',    '08567891234', 'Jl. Melati No. 12, Surakarta',  46000, 'confirmed', NULL),
('Agus Priyanto',  '08987654321', 'Jl. Mawar No. 3, Surakarta',    44500, 'pending',   'COD saja ya');

INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES
-- Order 1
(1, 1,  'Beras Pandan Wangi 5 kg',    65000, 1,  65000),
(1, 7,  'Gula Pasir Rose Brand 1 kg', 14500, 2,  29000),
(1, 6,  'Garam Refina 500 g',          5000, 1,   5000),
(1, 18, 'Sabun Mandi Lifebuoy 80 g',   4500, 2,   9000),
-- Order 2
(2, 4,  'Minyak Goreng Tropical 2 L',  32000, 1,  32000),
(2, 8,  'Kecap Manis ABC 135 ml',       8000, 1,   8000),
(2, 14, 'Wafer Tango Cokelat 130 g',   10000, 1,  10000),
-- Order 3
(3, 10, 'Teh Celup Sariwangi 50 pcs',  12000, 1,  12000),
(3, 11, 'Kopi Kapal Api Spesial 165 g',18000, 1,  18000),
(3, 6,  'Garam Refina 500 g',           5000, 1,   5000),
(3, 9,  'Terasi Udang Indofood 10 g',   3500, 1,   3500);
