<?php
// public/index.php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/config/database.php';
require_once dirname(__DIR__) . '/src/includes/helpers.php';

$db = getDB();

// Ambil produk unggulan (terbaru, per kategori)
$featuredProducts = $db->query(
    "SELECT p.id, p.name, p.price, p.image, p.slug, c.name AS category
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.is_active = 1
     ORDER BY p.created_at DESC LIMIT 8"
)->fetchAll();

$categories = $db->query(
    "SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
     WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.display_order"
)->fetchAll();

$appName   = $_ENV['APP_NAME'] ?? 'Toko Rini';
$address   = $_ENV['STORE_ADDRESS'] ?? 'Surakarta, Jawa Tengah';
$mapsUrl   = $_ENV['MAPS_EMBED_URL'] ?? 'https://maps.google.com/maps?q=-7.5755,110.8243&z=15&output=embed';
$pageTitle = 'Beranda';
require_once dirname(__DIR__) . '/src/includes/header_public.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">🛒 Toko Kelontong Terpercaya</div>
        <h1 class="hero-title">Selamat Datang di<br><span><?= e($appName) ?></span></h1>
        <p class="hero-desc">Melayani kebutuhan sehari-hari warga Surakarta dan sekitarnya sejak 2010.
            Produk lengkap, harga terjangkau, pelayanan ramah.</p>
        <div class="hero-actions">
            <a href="/products.php" class="btn-hero-primary">Lihat Produk</a>
            <a href="<?= e(waUrl('Halo Toko Rini! Saya ingin memesan produk.')) ?>"
               class="btn-hero-wa" target="_blank" rel="noopener noreferrer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Pesan via WA
            </a>
        </div>
    </div>
    <div class="hero-decoration">
        <div class="hero-blob"></div>
    </div>
</section>

<!-- Tentang Toko -->
<section class="about-section container">
    <div class="about-grid">
        <div class="about-text">
            <span class="section-tag">Tentang Kami</span>
            <h2>Toko Kelontong yang Sudah Dipercaya Selama Lebih dari 10 Tahun</h2>
            <p>Toko Rini berdiri sejak 2010, melayani kebutuhan sehari-hari mulai dari bahan makanan pokok,
               minuman, bumbu dapur, hingga kebutuhan rumah tangga. Kami berkomitmen memberikan produk
               berkualitas dengan harga yang bersaing dan pelayanan yang ramah.</p>
            <div class="about-visi">
                <div class="visi-item">
                    <span class="visi-icon">🎯</span>
                    <div>
                        <strong>Visi</strong>
                        <p>Menjadi toko kelontong terpercaya dan terlengkap di Surakarta.</p>
                    </div>
                </div>
                <div class="visi-item">
                    <span class="visi-icon">💪</span>
                    <div>
                        <strong>Misi</strong>
                        <p>Menyediakan kebutuhan sehari-hari dengan harga terjangkau dan pelayanan prima.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="about-stats">
            <div class="stat-item"><span class="stat-num">10+</span><span>Tahun Berpengalaman</span></div>
            <div class="stat-item"><span class="stat-num"><?= count($featuredProducts) ?>+</span><span>Jenis Produk</span></div>
            <div class="stat-item"><span class="stat-num"><?= count($categories) ?></span><span>Kategori</span></div>
            <div class="stat-item"><span class="stat-num">★4.9</span><span>Rating Pelanggan</span></div>
        </div>
    </div>
</section>

<!-- Kategori -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Produk Kami</span>
            <h2>Belanja Berdasarkan Kategori</h2>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="/products.php?category=<?= (int)$cat['id'] ?>" class="category-card">
                <div class="cat-count"><?= (int)$cat['product_count'] ?> produk</div>
                <div class="cat-name"><?= e($cat['name']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produk Unggulan -->
<section class="products-section container">
    <div class="section-header">
        <span class="section-tag">Terbaru</span>
        <h2>Produk Unggulan</h2>
        <a href="/products.php" class="section-link">Lihat Semua →</a>
    </div>
    <div class="products-grid">
        <?php foreach ($featuredProducts as $p): ?>
        <div class="product-card">
            <div class="product-img">
                <?php if ($p['image']): ?>
                <img src="/images/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                <?php else: ?>
                <div class="product-img-placeholder">📦</div>
                <?php endif; ?>
            </div>
            <div class="product-body">
                <div class="product-category"><?= e($p['category']) ?></div>
                <h3 class="product-name"><?= e($p['name']) ?></h3>
                <div class="product-price"><?= rupiah((float)$p['price']) ?></div>
                <a href="<?= e(waUrl("Halo Toko Rini, saya ingin memesan: {$p['name']}")) ?>"
                   class="btn-order" target="_blank" rel="noopener noreferrer">
                    Pesan via WA
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Lokasi -->
<section class="location-section">
    <div class="container">
        <div class="location-grid">
            <div class="location-info">
                <span class="section-tag">Temukan Kami</span>
                <h2>Kunjungi Toko Kami</h2>
                <p><?= e($address) ?></p>
                <div class="location-contact">
                    <a href="<?= e(waUrl('Halo Toko Rini!')) ?>" class="btn-wa-lg" target="_blank" rel="noopener noreferrer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Chat di WhatsApp
                    </a>
                </div>
            </div>
            <div class="location-map">
                <iframe src="<?= e($mapsUrl) ?>"
                        width="100%" height="300" style="border:0; border-radius:12px;"
                        allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/src/includes/footer_public.php'; ?>
