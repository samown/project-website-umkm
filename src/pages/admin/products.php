<?php
// src/pages/admin/products.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();

// Hapus produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Soft delete
        $db->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);
        setFlash('success', 'Produk berhasil dihapus.');
    }
    redirect('/admin/products.php');
}

// Filter & Pagination
$search  = trim($_GET['q'] ?? '');
$catId   = (int)($_GET['category'] ?? 0);
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where  = ['p.is_active = 1'];
$params = [];

if ($search !== '') {
    $where[]  = 'p.name LIKE ?';
    $params[] = "%{$search}%";
}
if ($catId > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catId;
}

$whereSQL = implode(' AND ', $where);

$total = (int) $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSQL}")
                  ->execute($params) ? $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSQL}") : 0;
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT p.id, p.name, p.price, p.stock, p.min_stock, p.image, p.is_active,
            c.name AS category
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE {$whereSQL}
     ORDER BY p.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order')->fetchAll();

$pageTitle = 'Manajemen Produk';
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<div class="toolbar">
    <form method="get" class="toolbar-search">
        <input type="text" name="q" class="form-control" placeholder="Cari produk..." value="<?= e($search) ?>">
        <select name="category" class="form-control">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if ($search || $catId): ?>
        <a href="/admin/products.php" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
    <a href="/admin/product_form.php" class="btn btn-primary">+ Tambah Produk</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" class="text-center text-muted">Tidak ada produk ditemukan.</td></tr>
            <?php else: foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['image']): ?>
                        <img src="/images/<?= e($p['image']) ?>" alt="" class="table-img">
                        <?php else: ?>
                        <div class="table-img-placeholder">📦</div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['category']) ?></td>
                    <td><?= rupiah((float)$p['price']) ?></td>
                    <td>
                        <span class="<?= (int)$p['stock'] <= (int)$p['min_stock'] ? 'text-danger font-bold' : '' ?>">
                            <?= (int)$p['stock'] ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="/admin/product_form.php?id=<?= (int)$p['id'] ?>"
                           class="btn btn-xs btn-outline">Edit</a>
                        <form method="post" class="inline"
                              onsubmit="return confirm('Hapus produk ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&category=<?= $catId ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
