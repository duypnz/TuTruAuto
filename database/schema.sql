CREATE DATABASE IF NOT EXISTS tu_tru_auto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tu_tru_auto;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    status ENUM('active','inactive','locked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cars (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    brand VARCHAR(80) NOT NULL,
    year SMALLINT UNSIGNED NOT NULL,
    body_type VARCHAR(40) NOT NULL,
    fuel VARCHAR(40) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    price DECIMAL(15,0) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    badge VARCHAR(80) NULL,
    description TEXT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('available','reserved','sold','hidden') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cars_catalog(status, featured, created_at),
    INDEX idx_cars_brand(brand),
    INDEX idx_cars_body(body_type),
    INDEX idx_cars_fuel(fuel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
