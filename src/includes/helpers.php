<?php
// src/includes/helpers.php

declare(strict_types=1);

/** Escape output untuk mencegah XSS */
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format harga ke Rupiah */
function rupiah(int|float $amount): string {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

/** Redirect ke URL */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/** Cek apakah admin sudah login */
function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/** Proteksi halaman admin — redirect ke login jika belum login */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        redirect('/auth/login.php');
    }
}

/** Buat slug dari string */
function makeSlug(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/** Nomor order unik */
function generateOrderNumber(): string {
    return 'TK' . date('Ymd') . str_pad((string)random_int(1, 999), 3, '0', STR_PAD_LEFT);
}

/** Flash message ke session */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil dan hapus flash message */
function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Upload gambar — return nama file atau null jika gagal */
function uploadImage(array $file, string $dest): ?string {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowedTypes, true)) return null;
    if ($file['size'] > $maxSize) return null;

    // Validasi mime type lebih ketat
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, $allowedTypes, true)) return null;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $destPath = rtrim($dest, '/') . '/' . $filename;

    if (!is_dir($dest)) mkdir($dest, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $destPath)) return null;

    return $filename;
}

/** Base URL aplikasi */
function baseUrl(string $path = ''): string {
    $appUrl = $_ENV['APP_URL'] ?? '';
    return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
}

/** Nomor WhatsApp dari env */
function waUrl(string $message = ''): string {
    $number = $_ENV['WHATSAPP_NUMBER'] ?? '6285249296758';
    $msg    = $message ? '?text=' . urlencode($message) : '';
    return "https://wa.me/{$number}{$msg}";
}
