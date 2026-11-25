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
    discount_price DECIMAL(15,0) NULL,
    stock_quantity INT UNSIGNED NULL DEFAULT NULL,
    lead_time_days SMALLINT UNSIGNED NOT NULL DEFAULT 21,
    image_url VARCHAR(500) NOT NULL,
    gallery_json LONGTEXT NULL,
    video_url VARCHAR(1000) NULL,
    details_json LONGTEXT NULL,
    badge VARCHAR(80) NULL,
    description TEXT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('available','reserved','sold','hidden') NOT NULL DEFAULT 'available',
    condition_type ENUM('new', 'used') NOT NULL DEFAULT 'new',
    car_code VARCHAR(40) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cars_catalog(status, featured, created_at),
    INDEX idx_cars_brand(brand),
    INDEX idx_cars_body(body_type),
    INDEX idx_cars_fuel(fuel),
    UNIQUE KEY uq_cars_car_code (car_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS car_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_car_review_user (car_id, user_id),
    CONSTRAINT fk_car_reviews_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_car_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS car_vouchers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(15,0) NOT NULL,
    minimum_order DECIMAL(15,0) NOT NULL DEFAULT 0,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS car_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    inquiry_type ENUM('buy', 'deposit', 'test_drive', 'quote', 'advice', 'contact') NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(190) NULL,
    message TEXT NULL,
    inquiry_status ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_car_inquiries_created (created_at),
    INDEX idx_car_inquiries_status (inquiry_status, created_at),
    CONSTRAINT fk_car_inquiries_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
    CONSTRAINT fk_car_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS car_catalog_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_type ENUM('brand', 'body_type') NOT NULL,
    name VARCHAR(80) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_car_catalog_option (option_type, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value VARCHAR(500) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    order_type ENUM('purchase', 'deposit') NOT NULL,
    status ENUM('pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled') NOT NULL DEFAULT 'pending',
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    message TEXT NULL,
    voucher_code VARCHAR(40) NULL,
    subtotal DECIMAL(15,0) NOT NULL,
    discount_total DECIMAL(15,0) NOT NULL DEFAULT 0,
    total DECIMAL(15,0) NOT NULL,
    deposit_amount DECIMAL(15,0) NULL,
    delivery_at DATETIME NULL,
    payment_info_json TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_orders_user (user_id, created_at),
    INDEX idx_customer_orders_status (status, created_at),
    CONSTRAINT fk_customer_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    car_id BIGINT UNSIGNED NULL,
    car_name VARCHAR(180) NOT NULL,
    car_image VARCHAR(500) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    unit_price DECIMAL(15,0) NOT NULL,
    options_json LONGTEXT NOT NULL,
    CONSTRAINT fk_customer_order_items_order FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_order_items_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
