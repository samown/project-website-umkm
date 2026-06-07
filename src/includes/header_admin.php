<?php
// src/includes/header_admin.php
declare(strict_types=1);

if (!isset($pageTitle)) $pageTitle = 'Admin Panel';
$appName = $_ENV['APP_NAME'] ?? 'Toko Rini';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Admin <?= e($appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span>🛒</span>
            <span><?= e($appName) ?></span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="/admin/products.php" class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php','product_form.php']) ? 'active' : '' ?>">
                <span class="nav-icon">📦</span> Produk
            </a>
            <a href="/admin/categories.php" class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['categories.php','category_form.php']) ? 'active' : '' ?>">
                <span class="nav-icon">🏷️</span> Kategori
            </a>
            <a href="/admin/orders.php" class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['orders.php','order_detail.php']) ? 'active' : '' ?>">
                <span class="nav-icon">📋</span> Pesanan
            </a>
            <div class="nav-divider"></div>
            <a href="/index.php" class="nav-item" target="_blank">
                <span class="nav-icon">🌐</span> Lihat Website
            </a>
            <a href="/auth/logout.php" class="nav-item nav-logout">
                <span class="nav-icon">🚪</span> Keluar
            </a>
        </nav>
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
            <div class="user-info">
                <span class="user-name"><?= e($adminName) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </aside>
    <!-- Main content -->
    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
        </div>
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>
        <div class="admin-content">
