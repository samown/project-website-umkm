-- =============================================================
-- seed.sql — Data Awal Toko Rini
-- Jalankan SETELAH schema.sql:
--   mysql -u root -p toko_rini < database/seed.sql
-- =============================================================

USE toko_rini;

-- ============================================================
-- Admin Default
-- ============================================================
-- Username: admin | Password: admin123
-- Bcrypt Hash: $2b$12$nOUIs5kJ7naTuTFkBy1H2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUi
-- Generate hash dengan:
--   python3 -c "import bcrypt; print(bcrypt.hashpw(b'admin123', bcrypt.gensalt()).decode())"
-- ============================================================
INSERT INTO admins (username, password, full_name, phone_number, is_active) VALUES
(
    'admin',
    '$2b$12$CXc3UQ8.RwnHyhtaX/MpneShrAgnPlL09xq01Z40uE2QM2P2SnQju',
    'Rini Daswati',
    '085249296758',
    1
);

-- ============================================================
-- Kategori Produk Kelontong (Minimal 3)
-- ============================================================
INSERT INTO categories (name, slug, description, display_order, is_active) VALUES
('Makanan Pokok', 'makanan-pokok', 'Beras, tepung, biji-bijian, dan kebutuhan pokok lainnya', 1, 1),
('Minuman', 'minuman', 'Teh, kopi, sirup, dan minuman siap minum', 2, 1),
('Kebutuhan Rumah', 'kebutuhan-rumah', 'Sabun, deterjen, plastik, dan keperluan rumah tangga lainnya', 3, 1),
('Bumbu & Rempah', 'bumbu-rempah', 'Garam, gula, kecap, bumbu, dan rempah-rempah', 4, 1),
('Snack & Camilan', 'snack-camilan', 'Keripik, biskuit, wafer, dan snack lainnya', 5, 1);

-- ============================================================
-- Produk (Minimal 5, Distribusi di Kategori)
-- ============================================================
-- Kategori 1: Makanan Pokok (id=1)
INSERT INTO products (category_id, name, slug, description, price, cost, stock, min_stock, is_active) VALUES
(1, 'Beras Pandan Wangi 5 kg', 'beras-pandan-wangi-5kg', 
 'Beras premium pulen dan wangi, cocok untuk nasi putih berkualitas sehari-hari. Dari panen terpilih.', 
 65000, 58000, 50, 5, 1),

(1, 'Beras IR64 10 kg', 'beras-ir64-10kg', 
 'Beras medium dengan giling putih yang rata. Ekonomis untuk kebutuhan keluarga besar.', 
 95000, 87000, 40, 3, 1),

(1, 'Tepung Terigu Protein Tinggi 1 kg', 'tepung-terigu-protein-tinggi-1kg', 
 'Tepung terigu berkualitas tinggi dengan protein >12% untuk roti dan kue yang sempurna.', 
 18000, 16000, 35, 5, 1),

(1, 'Kacang Hijau 500 g', 'kacang-hijau-500g', 
 'Kacang hijau pilihan yang cerah dan utuh. Bagus untuk bubur kacang hijau dan minuman.', 
 14000, 11000, 60, 8, 1),

(1, 'Gula Pasir Premium 1 kg', 'gula-pasir-premium-1kg', 
 'Gula pasir putih halus berkualitas, manis alami tanpa bau aneh.', 
 14500, 13000, 90, 10, 1);

-- Kategori 2: Minuman (id=2)
INSERT INTO products (category_id, name, slug, description, price, cost, stock, min_stock, is_active) VALUES
(2, 'Teh Celup Sariwangi 50 sachet', 'teh-celup-sariwangi-50', 
 'Teh hitam pilihan dalam kemasan celup praktis. Rasa nikmat dengan aroma khas yang kuat.', 
 12000, 10000, 100, 10, 1),

(2, 'Kopi Kapal Api Spesial 165 g', 'kopi-kapal-api-spesial-165g', 
 'Kopi bubuk robusta pilihan dengan aroma kuat dan rasa nikmat yang mendalam.', 
 18000, 15500, 75, 8, 1),

(2, 'Sirup Marjan Cocopandan 460 ml', 'sirup-marjan-cocopandan-460ml', 
 'Sirup segar dengan rasa istimewa cocopandan untuk minuman dingin menyegarkan.', 
 24000, 20000, 50, 5, 1);

-- Kategori 3: Kebutuhan Rumah (id=3)
INSERT INTO products (category_id, name, slug, description, price, cost, stock, min_stock, is_active) VALUES
(3, 'Sabun Cuci Sunlight 755 ml', 'sabun-cuci-sunlight-755ml', 
 'Sabun cuci piring liquid dengan formula anti lemak dan aroma jeruk yang segar.', 
 18000, 15000, 70, 8, 1),

(3, 'Sabun Mandi Lifebuoy 80 g', 'sabun-mandi-lifebuoy-80g', 
 'Sabun batang dengan formula antiseptik untuk perlindungan kuman yang maksimal.', 
 4500, 3500, 200, 20, 1),

(3, 'Plastik Bungkus Kiloan 1 kg', 'plastik-bungkus-kiloan-1kg', 
 'Plastik bungkus tebal dan kuat ukuran 30x40 cm. Cocok untuk berbagai keperluan.', 
 15000, 12000, 40, 5, 1);

-- Kategori 4: Bumbu & Rempah (id=4)
INSERT INTO products (category_id, name, slug, description, price, cost, stock, min_stock, is_active) VALUES
(4, 'Garam Refina 500 g', 'garam-refina-500g', 
 'Garam beryodium halus untuk masak sehari-hari dan pengawet makanan.', 
 5000, 3500, 150, 15, 1),

(4, 'Kecap Manis ABC 135 ml', 'kecap-manis-abc-135ml', 
 'Kecap manis pekat dengan rasa khas Indonesia yang cocok untuk semua masakan.', 
 8000, 6500, 120, 15, 1),

(4, 'Terasi Udang Indofood 10 g', 'terasi-udang-indofood-10g', 
 'Terasi udang asli dengan aroma yang aromatis dan rasa gurih yang sempurna.', 
 3500, 2500, 200, 25, 1);

-- Kategori 5: Snack & Camilan (id=5)
INSERT INTO products (category_id, name, slug, description, price, cost, stock, min_stock, is_active) VALUES
(5, 'Keripik Singkong Pedas 200 g', 'keripik-singkong-pedas-200g', 
 'Keripik singkong gurih dan pedas yang renyah. Camilan favorit keluarga Indonesia.', 
 12000, 9500, 60, 8, 1),

(5, 'Wafer Tango Cokelat 130 g', 'wafer-tango-cokelat-130g', 
 'Wafer berlapis cokelat yang lezat dengan tekstur renyah dan lembut.', 
 10000, 7500, 90, 10, 1),

(5, 'Biskuit Marie Regal 300 g', 'biskuit-marie-regal-300g', 
 'Biskuit renyah klasik Indonesia. Enak dimakan langsung atau dicelup dengan teh dan kopi.', 
 14000, 11000, 80, 10, 1);

-- ============================================================
-- Data Pesanan Sample (untuk demo dashboard)
-- ============================================================
INSERT INTO orders (order_number, customer_name, customer_phone, customer_address, total_price, total_items, payment_method, status, notes) VALUES
('TK20250601001', 'Budi Santoso', '08123456789', 'Jl. Kenanga No. 5, Surakarta', 111000, 4, 'cash', 'completed', 'Minta kantong plastik ya'),
('TK20250601002', 'Siti Rahayu', '08567891234', 'Jl. Melati No. 12, Surakarta', 46000, 3, 'transfer', 'confirmed', NULL),
('TK20250601003', 'Agus Priyanto', '08987654321', 'Jl. Mawar No. 3, Surakarta', 44500, 3, 'cash', 'pending', 'COD saja');

-- ============================================================
-- Detail Order Items
-- ============================================================
INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES
-- Order 1 (TK20250601001)
(1, 1, 'Beras Pandan Wangi 5 kg', 65000, 1, 65000),
(1, 5, 'Gula Pasir Premium 1 kg', 14500, 2, 29000),
(1, 10, 'Garam Refina 500 g', 5000, 1, 5000),
(1, 9, 'Sabun Mandi Lifebuoy 80 g', 4500, 2, 9000),

-- Order 2 (TK20250601002)
(2, 12, 'Sirup Marjan Cocopandan 460 ml', 24000, 1, 24000),
(2, 14, 'Kecap Manis ABC 135 ml', 8000, 1, 8000),
(2, 17, 'Wafer Tango Cokelat 130 g', 10000, 1, 10000),

-- Order 3 (TK20250601003)
(3, 6, 'Teh Celup Sariwangi 50 sachet', 12000, 1, 12000),
(3, 7, 'Kopi Kapal Api Spesial 165 g', 18000, 1, 18000),
(3, 10, 'Garam Refina 500 g', 5000, 1, 5000),
(3, 15, 'Terasi Udang Indofood 10 g', 3500, 1, 3500),
(3, 16, 'Keripik Singkong Pedas 200 g', 12000, 1, 12000);

-- ============================================================
-- Verifikasi Data
-- ============================================================
-- SELECT 'Admin' as type, COUNT(*) as count FROM admins
-- UNION ALL
-- SELECT 'Categories', COUNT(*) FROM categories
-- UNION ALL
-- SELECT 'Products', COUNT(*) FROM products
-- UNION ALL
-- SELECT 'Orders', COUNT(*) FROM orders
-- UNION ALL
-- SELECT 'Order Items', COUNT(*) FROM order_items;
