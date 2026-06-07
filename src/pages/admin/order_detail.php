<?php
// src/pages/admin/order_detail.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Pesanan tidak ditemukan.');
    redirect('/admin/orders.php');
}

// Update catatan admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_notes') {
    $notes = trim($_POST['admin_notes'] ?? '');
    $db->prepare('UPDATE orders SET admin_notes = ? WHERE id = ?')->execute([$notes, $id]);
    setFlash('success', 'Catatan admin disimpan.');
    redirect('/admin/order_detail.php?id=' . $id);
}

$items = $db->prepare(
    'SELECT oi.*, p.image FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?'
);
$items->execute([$id]);
$orderItems = $items->fetchAll();

$pageTitle = 'Detail Pesanan #' . $order['order_number'];
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<div class="toolbar">
    <a href="/admin/orders.php" class="btn btn-ghost">← Kembali</a>
</div>

<div class="order-detail-grid">
    <div class="card">
        <div class="card-header"><h2>Info Pesanan</h2></div>
        <div class="detail-table">
            <div class="detail-row"><span>No. Order</span><strong><code><?= e($order['order_number']) ?></code></strong></div>
            <div class="detail-row"><span>Status</span><span class="badge badge-<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span></div>
            <div class="detail-row"><span>Pembayaran</span><span><?= ucfirst(e($order['payment_method'])) ?></span></div>
            <div class="detail-row"><span>Tanggal</span><span><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span></div>
            <div class="detail-row"><span>Total</span><strong><?= rupiah((float)$order['total_price']) ?></strong></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>Info Pelanggan</h2></div>
        <div class="detail-table">
            <div class="detail-row"><span>Nama</span><span><?= e($order['customer_name']) ?></span></div>
            <div class="detail-row"><span>Telepon</span>
                <a href="<?= e(waUrl("Halo {$order['customer_name']}, terkait pesanan {$order['order_number']}")) ?>"
                   target="_blank" class="btn-wa-inline">
                    <?= e($order['customer_phone']) ?>
                </a>
            </div>
            <?php if ($order['customer_email']): ?>
            <div class="detail-row"><span>Email</span><span><?= e($order['customer_email']) ?></span></div>
            <?php endif; ?>
            <div class="detail-row"><span>Alamat</span><span><?= e($order['customer_address']) ?></span></div>
            <?php if ($order['notes']): ?>
            <div class="detail-row"><span>Catatan</span><span><?= e($order['notes']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Item Pesanan</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($orderItems as $item): ?>
            <tr>
                <td>
                    <?php if ($item['image']): ?>
                    <img src="/images/<?= e($item['image']) ?>" class="table-img" alt="">
                    <?php endif; ?>
                    <?= e($item['product_name']) ?>
                </td>
                <td><?= rupiah((float)$item['price']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= rupiah((float)$item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3"><strong>Total</strong></td>
                <td><strong><?= rupiah((float)$order['total_price']) ?></strong></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Catatan Admin</h2></div>
    <form method="post">
        <input type="hidden" name="action" value="save_notes">
        <div class="form-group p-4">
            <textarea name="admin_notes" class="form-control" rows="4"
                      placeholder="Tambahkan catatan internal untuk pesanan ini..."><?= e($order['admin_notes'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Catatan</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
