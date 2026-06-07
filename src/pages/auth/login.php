<?php
// src/pages/auth/login.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

session_start();

// Jika sudah login, arahkan ke dashboard
if (isAdminLoggedIn()) {
    redirect('/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, username, password, full_name, is_active FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && $admin['is_active'] && password_verify($password, $admin['password'])) {
            // Login berhasil
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_user'] = $admin['username'];

            // Update last_login
            $db->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$admin['id']]);

            redirect('/admin/dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Toko Rini</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">🛒</div>
            <h1>Toko Rini</h1>
            <p>Masuk ke Panel Admin</p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <div class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?= e($_POST['username'] ?? '') ?>"
                       autocomplete="username" required autofocus
                       form="login-form">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       autocomplete="current-password" required
                       form="login-form">
            </div>
            <form id="login-form" method="post" action="">
                <button type="submit" class="btn btn-primary btn-full">Masuk</button>
            </form>
        </div>
        <div class="login-footer">
            <a href="/index.php">← Kembali ke Website</a>
        </div>
    </div>
</body>
</html>
