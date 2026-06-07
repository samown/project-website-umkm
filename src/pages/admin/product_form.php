<?php
// src/pages/admin/product_form.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Ambil data produk untuk edit
$product = [];
if ($isEdit) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        setFlash('danger', 'Produk tidak ditemukan.');
        redirect('/admin/products.php');
    }
}

$categories = $db->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $name       = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description= trim($_POST['description'] ?? '');
    $price      = (int)($_POST['price'] ?? 0);
    $cost       = (int)($_POST['cost'] ?? 0);
    $stock      = (int)($_POST['stock'] ?? 0);
    $minStock   = (int)($_POST['min_stock'] ?? 5);
    $slug       = trim($_POST['slug'] ?? '') ?: makeSlug($name);

    // Validasi
    if (empty($name)) $errors[] = 'Nama produk wajib diisi.';
    if ($categoryId <= 0) $errors[] = 'Kategori wajib dipilih.';
    if ($price <= 0) $errors[] = 'Harga harus lebih dari 0.';

    // Upload gambar
    $imageName = $isEdit ? ($product['image'] ?? null) : null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = dirname(__DIR__, 3) . '/public/images/';
        $newImage  = uploadImage($_FILES['image'], $uploadDir);
        if ($newImage === null) {
            $errors[] = 'Gambar tidak valid. Gunakan JPG/PNG/WebP maks 2MB.';
        } else {
            // Hapus gambar lama
            if ($isEdit && $imageName && file_exists($uploadDir . $imageName)) {
                unlink($uploadDir . $imageName);
            }
            $imageName = $newImage;
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $db->prepare(
                'UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, cost=?, stock=?, min_stock=?, image=?, updated_at=NOW()
                 WHERE id=?'
            );
            $stmt->execute([$name, $slug, $categoryId, $description, $price, $cost, $stock, $minStock, $imageName, $id]);
            setFlash('success', 'Produk berhasil diperbarui.');
        } else {
            $stmt = $db->prepare(
                'INSERT INTO products (name, slug, category_id, description, price, cost, stock, min_stock, image, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([$name, $slug, $categoryId, $description, $price, $cost, $stock, $minStock, $imageName]);
            setFlash('success', 'Produk berhasil ditambahkan.');
        }
        redirect('/admin/products.php');
    }
}

$pageTitle = $isEdit ? 'Edit Produk' : 'Tambah Produk';
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="card form-card">
    <form method="post" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Produk <span class="required">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= e($_POST['name'] ?? $product['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori <span class="required">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">— Pilih Kategori —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                        <?= ((int)($_POST['category_id'] ?? $product['category_id'] ?? 0)) === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-full">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="4"><?= e($_POST['description'] ?? $product['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Harga Jual (Rp) <span class="required">*</span></label>
                <input type="number" name="price" class="form-control" min="0"
                       value="<?= e($_POST['price'] ?? $product['price'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Harga Pokok (Rp)</label>
                <input type="number" name="cost" class="form-control" min="0"
                       value="<?= e($_POST['cost'] ?? $product['cost'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="form-control" min="0"
                       value="<?= e($_POST['stock'] ?? $product['stock'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label>Stok Minimum (Alert)</label>
                <input type="number" name="min_stock" class="form-control" min="0"
                       value="<?= e($_POST['min_stock'] ?? $product['min_stock'] ?? 5) ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" class="form-control"
                       value="<?= e($_POST['slug'] ?? $product['slug'] ?? '') ?>"
                       placeholder="Kosongkan untuk otomatis">
            </div>
            <div class="form-group form-full">
                <label>Gambar Produk (JPG/PNG/WebP, maks 2MB)</label>
                <?php if ($isEdit && !empty($product['image'])): ?>
                <div class="current-image">
                    <img src="/images/<?= e($product['image']) ?>" alt="Gambar saat ini" class="preview-img">
                    <small>Gambar saat ini — upload baru untuk mengganti</small>
                </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>
        <div class="form-actions">
            <a href="/admin/products.php" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
