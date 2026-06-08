<?php
// src/config/database.php
// Koneksi database menggunakan PDO + .env manual loader

declare(strict_types=1);

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strncmp(trim($line), '#', 1) === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// .env is now at public_html/.env (one level above src/config/)
loadEnv(dirname(__DIR__) . '/.env');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host   = $_ENV['DB_HOST']   ?? 'localhost';
    $port   = $_ENV['DB_PORT']   ?? '3306';
    $dbname = $_ENV['DB_NAME']   ?? 'toko_rini';
    $user   = $_ENV['DB_USER']   ?? 'root';
    $pass   = $_ENV['DB_PASS']   ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Jangan tampilkan detail error ke user di production
        error_log('DB Connection Error: ' . $e->getMessage());
        http_response_code(500);
        die('Koneksi database gagal. Silakan coba lagi nanti.');
    }

    return $pdo;
}
