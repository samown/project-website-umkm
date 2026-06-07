<?php
// src/pages/admin/categories.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();

// Hapus kategori (soft delete jika tidak ada produk)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $count = (int)$db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1')
                          ->execute([$id]) ? 0 : 0;
        $countStmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1');
        $countStmt->execute([$id]);
        $count = (int)$countStmt->fetchColumn();

        if ($count > 0) {
            setFlash('danger', "Kategori tidak bisa dihapus karena memiliki {$count} produk aktif.");
        } else {
            $db->prepare('UPDATE categories SET is_active = 0 WHERE id = ?')->execute([$id]);
            setFlash('success', 'Kategori berhasil dihapus.');
        }
    }
    redirect('/admin/categories.php');
}

// Tambah / Edit kategori via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $catId       = (int)($_POST['cat_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $order       = (int)($_POST['display_order'] ?? 0);
    $slug        = trim($_POST['slug'] ?? '') ?: makeSlug($name);

    if (!empty($name)) {
        if ($catId > 0) {
            $db->prepare('UPDATE categories SET name=?, slug=?, description=?, display_order=? WHERE id=?')
               ->execute([$name, $slug, $description, $order, $catId]);
            setFlash('success', 'Kategori berhasil diperbarui.');
        } else {
            $db->prepare('INSERT INTO categories (name, slug, description, display_order, is_active) VALUES (?,?,?,?,1)')
               ->execute([$name, $slug, $description, $order]);
            setFlash('success', 'Kategori berhasil ditambahkan.');
        }
    }
    redirect('/admin/categories.php');
}

$categories = $db->query(
    "SELECT c.id, c.name, c.slug, c.description, c.display_order,
            COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.display_order, c.name"
)->fetchAll();

// Edit data
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}

$pageTitle = 'Manajemen Kategori';
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<div class="two-col-layout">
    <!-- Form -->
    <div class="card form-card">
        <h2><?= $editCat ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="cat_id" value="<?= $editCat ? (int)$editCat['id'] : 0 ?>">
            <div class="form-group">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" required
                       value="<?= e($editCat['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" placeholder="Otomatis dari nama"
                       value="<?= e($editCat['slug'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= e($editCat['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Urutan Tampil</label>
                <input type="number" name="display_order" class="form-control" min="0"
                       value="<?= (int)($editCat['display_order'] ?? 0) ?>">
            </div>
            <div class="form-actions">
                <?php if ($editCat): ?>
                <a href="/admin/categories.php" class="btn btn-ghost">Batal</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><?= $editCat ? 'Simpan' : 'Tambah' ?></button>
            </div>
        </form>
    </div>

    <!-- List -->
    <div class="card">
        <div class="card-header"><h2>Daftar Kategori</h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Nama</th><th>Slug</th><th>Produk</th><th>Urutan</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada kategori.</td></tr>
                <?php else: foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= e($cat['name']) ?></td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td><?= (int)$cat['product_count'] ?></td>
                        <td><?= (int)$cat['display_order'] ?></td>
                        <td class="actions">
                            <a href="?edit=<?= (int)$cat['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
                            <form method="post" class="inline"
                                  onsubmit="return confirm('Hapus kategori ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
