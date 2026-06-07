<?php
// src/pages/public/products.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

$db = getDB();

$search  = trim($_GET['q'] ?? '');
$catId   = (int)($_GET['category'] ?? 0);
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 16;
$offset  = ($page - 1) * $perPage;

$where  = ['p.is_active = 1'];
$params = [];

if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($catId > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catId;
}

$whereSQL = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSQL}");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT p.id, p.name, p.price, p.image, p.description, c.name AS category, c.id AS cat_id
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE {$whereSQL}
     ORDER BY p.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order')->fetchAll();

// Nama kategori aktif
$activeCatName = '';
if ($catId > 0) {
    foreach ($categories as $cat) {
        if ((int)$cat['id'] === $catId) { $activeCatName = $cat['name']; break; }
    }
}

$pageTitle = $activeCatName ?: ($search ? "Pencarian: {$search}" : 'Semua Produk');
require_once dirname(__DIR__, 3) . '/src/includes/header_public.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>🛍️ <?= e($pageTitle) ?></h1>
        <p>Temukan kebutuhan sehari-hari Anda di sini</p>
    </div>
</div>

<div class="container products-page">
    <!-- Filter bar -->
    <div class="filter-bar">
        <form method="get" class="filter-form">
            <input type="text" name="q" class="form-control" placeholder="Cari produk..."
                   value="<?= e($search) ?>">
            <select name="category" class="form-control">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-filter">Cari</button>
            <?php if ($search || $catId): ?>
            <a href="/products.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
        <div class="result-count"><?= $total ?> produk ditemukan</div>
    </div>

    <!-- Grid Produk -->
    <?php if (empty($products)): ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h2>Produk tidak ditemukan</h2>
        <p>Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
        <a href="/products.php" class="btn-outline-dark">Lihat Semua Produk</a>
    </div>
    <?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $p): ?>
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
                <?php if ($p['description']): ?>
                <p class="product-desc"><?= e(mb_strimwidth($p['description'], 0, 80, '…')) ?></p>
                <?php endif; ?>
                <div class="product-price"><?= rupiah((float)$p['price']) ?></div>
                <a href="<?= e(waUrl("Halo Toko Rini, saya ingin memesan: {$p['name']} (Rp " . number_format((float)$p['price'], 0, ',', '.') . ")")) ?>"
                   class="btn-order" target="_blank" rel="noopener noreferrer">
                    🛒 Pesan via WA
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination pub-pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&q=<?= urlencode($search) ?>&category=<?= $catId ?>" class="page-btn">‹</a>
        <?php endif; ?>
        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&category=<?= $catId ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&q=<?= urlencode($search) ?>&category=<?= $catId ?>" class="page-btn">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_public.php'; ?>
