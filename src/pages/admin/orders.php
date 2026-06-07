<?php
// src/pages/admin/orders.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $allowed   = ['pending','confirmed','processing','completed','cancelled'];
    if ($orderId > 0 && in_array($newStatus, $allowed, true)) {
        $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $orderId]);
        setFlash('success', 'Status pesanan diperbarui.');
    }
    redirect('/admin/orders.php');
}

// Filter
$status   = $_GET['status'] ?? '';
$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;
$offset   = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
$allowed = ['pending','confirmed','processing','completed','cancelled'];

if ($status !== '' && in_array($status, $allowed, true)) {
    $where[]  = 'status = ?';
    $params[] = $status;
}
if ($search !== '') {
    $where[]  = '(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$whereSQL = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE {$whereSQL}");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT id, order_number, customer_name, customer_phone, total_price, total_items, status, payment_method, created_at
     FROM orders WHERE {$whereSQL}
     ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Manajemen Pesanan';
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<div class="toolbar">
    <form method="get" class="toolbar-search">
        <input type="text" name="q" class="form-control" placeholder="Cari order/nama/telepon..."
               value="<?= e($search) ?>">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <?php foreach ($allowed as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if ($search || $status): ?>
        <a href="/admin/orders.php" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Telepon</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="text-center text-muted">Tidak ada pesanan ditemukan.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><code><?= e($o['order_number']) ?></code></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= e($o['customer_phone']) ?></td>
                    <td><?= rupiah((float)$o['total_price']) ?></td>
                    <td><?= ucfirst(e($o['payment_method'])) ?></td>
                    <td>
                        <form method="post" class="inline-status">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                            <select name="status" class="status-select status-<?= e($o['status']) ?>"
                                    onchange="this.form.submit()">
                                <?php foreach ($allowed as $s): ?>
                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <a href="/admin/order_detail.php?id=<?= (int)$o['id'] ?>"
                           class="btn btn-xs btn-outline">Detail</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
