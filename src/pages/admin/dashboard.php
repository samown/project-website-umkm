<?php
// src/pages/admin/dashboard.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();
requireAdminLogin();

$db = getDB();

// Statistik
$stats = [
    'products'   => (int) $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
    'categories' => (int) $db->query('SELECT COUNT(*) FROM categories WHERE is_active = 1')->fetchColumn(),
    'orders'     => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'pending'    => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'revenue'    => (float) $db->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status = 'completed'")->fetchColumn(),
    'low_stock'  => (int) $db->query('SELECT COUNT(*) FROM products WHERE stock <= min_stock AND is_active = 1')->fetchColumn(),
];

// Pesanan terbaru
$recentOrders = $db->query(
    "SELECT id, order_number, customer_name, total_price, status, created_at
     FROM orders ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// Produk stok rendah
$lowStockProducts = $db->query(
    "SELECT p.name, p.stock, p.min_stock, c.name AS category
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.stock <= p.min_stock AND p.is_active = 1
     ORDER BY p.stock ASC LIMIT 5"
)->fetchAll();

$pageTitle = 'Dashboard';
require_once dirname(__DIR__, 3) . '/src/includes/header_admin.php';
?>

<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">📦</div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['products'] ?></div>
            <div class="stat-label">Total Produk Aktif</div>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">📋</div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['orders'] ?></div>
            <div class="stat-label">Total Pesanan</div>
        </div>
    </div>
    <div class="stat-card stat-yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['pending'] ?></div>
            <div class="stat-label">Pesanan Pending</div>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">💰</div>
        <div class="stat-body">
            <div class="stat-value"><?= rupiah($stats['revenue']) ?></div>
            <div class="stat-label">Total Pendapatan</div>
        </div>
    </div>
    <?php if ($stats['low_stock'] > 0): ?>
    <div class="stat-card stat-red">
        <div class="stat-icon">⚠️</div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['low_stock'] ?></div>
            <div class="stat-label">Produk Stok Rendah</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <!-- Pesanan Terbaru -->
    <div class="card">
        <div class="card-header">
            <h2>Pesanan Terbaru</h2>
            <a href="/admin/orders.php" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada pesanan</td></tr>
                <?php else: foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><code><?= e($order['order_number']) ?></code></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td><?= rupiah((float)$order['total_price']) ?></td>
                        <td><span class="badge badge-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
                        <td><a href="/admin/order_detail.php?id=<?= (int)$order['id'] ?>" class="btn btn-xs btn-outline">Detail</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stok Rendah -->
    <div class="card">
        <div class="card-header">
            <h2>⚠️ Stok Rendah</h2>
            <a href="/admin/products.php" class="btn btn-sm btn-outline">Kelola Produk</a>
        </div>
        <?php if (empty($lowStockProducts)): ?>
            <p class="text-center text-muted p-4">Semua stok aman ✅</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Produk</th><th>Kategori</th><th>Stok</th><th>Min</th></tr></thead>
                <tbody>
                <?php foreach ($lowStockProducts as $p): ?>
                    <tr>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e($p['category']) ?></td>
                        <td><strong class="text-danger"><?= (int)$p['stock'] ?></strong></td>
                        <td><?= (int)$p['min_stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_admin.php'; ?>
