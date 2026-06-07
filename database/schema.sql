-- =============================================================
-- schema.sql — Toko Rini Database Schema
-- Toko Kelontong Online - Backend: Python Flask, Database: MySQL
-- Raw SQL (Tanpa ORM)
-- =============================================================

CREATE DATABASE IF NOT EXISTS toko_rini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toko_rini;

-- ============================================================
-- Tabel 1: admins
-- Menyimpan akun pengelola toko
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hash (60 chars)
    full_name   VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    last_login  DATETIME DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- Tabel 2: categories
-- Kategori produk kelontong
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon        VARCHAR(50),                   -- nama icon/emoji
    display_order INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- Tabel 3: products
-- Produk yang dijual di toko
-- FK: category_id → categories(id)
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    name         VARCHAR(150) NOT NULL,
    slug         VARCHAR(150),
    description  TEXT,
    price        DECIMAL(12,0) NOT NULL DEFAULT 0,
    cost         DECIMAL(12,0) DEFAULT 0,      -- harga pokok
    stock        INT NOT NULL DEFAULT 0,
    min_stock    INT DEFAULT 5,                -- stok minimal untuk alert
    image        VARCHAR(255)  DEFAULT NULL,   -- nama file gambar
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB;

-- ============================================================
-- Tabel 4: orders
-- Header pesanan dari pelanggan
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(50) NOT NULL UNIQUE,  -- contoh: TK20250601001
    customer_name   VARCHAR(150) NOT NULL,
    customer_phone  VARCHAR(20)  NOT NULL,
    customer_email  VARCHAR(100),
    customer_address TEXT        NOT NULL,
    total_price     DECIMAL(14,0) NOT NULL DEFAULT 0,
    total_items     INT NOT NULL DEFAULT 0,
    payment_method  ENUM('cash','transfer','card') NOT NULL DEFAULT 'cash',
    status          ENUM('pending','confirmed','processing','completed','cancelled')
                    NOT NULL DEFAULT 'pending',
    notes           TEXT,
    admin_notes     TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- Tabel 5: order_items
-- Detail item di dalam setiap pesanan
-- FK: order_id → orders(id), product_id → products(id)
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,   -- snapshot nama saat pesan
    price       DECIMAL(12,0) NOT NULL,   -- snapshot harga saat pesan
    quantity    INT          NOT NULL DEFAULT 1,
    subtotal    DECIMAL(14,0) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_order   FOREIGN KEY (order_id)
        REFERENCES orders(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB;

-- ============================================================
-- Indexes untuk performa
-- ============================================================
ALTER TABLE products ADD FULLTEXT idx_product_search (name, description);
