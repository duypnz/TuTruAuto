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

CREATE TABLE IF NOT EXISTS support_chat_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_support_chat_conversations_user (user_id),
    CONSTRAINT fk_support_chat_conversations_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_role ENUM('customer', 'admin') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_support_chat_messages_conversation (conversation_id, id),
    INDEX idx_support_chat_messages_unread (conversation_id, sender_role, is_read),
    CONSTRAINT fk_support_chat_messages_conversation
        FOREIGN KEY (conversation_id) REFERENCES support_chat_conversations(id) ON DELETE CASCADE
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
    name VARCHAR(120) NULL,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(15,0) NOT NULL,
    max_discount DECIMAL(15,0) NULL,
    minimum_order DECIMAL(15,0) NOT NULL DEFAULT 0,
    scope_type ENUM('all', 'brand', 'car') NOT NULL DEFAULT 'all',
    scope_value VARCHAR(190) NULL,
    user_id BIGINT UNSIGNED NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    usage_limit INT UNSIGNED NULL,
    usage_per_user INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_car_vouchers_lookup (is_active, deleted_at, ends_at),
    CONSTRAINT fk_car_vouchers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
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
    status ENUM('pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(190) NULL,
    message TEXT NULL,
    voucher_code VARCHAR(40) NULL,
    subtotal DECIMAL(15,0) NOT NULL,
    discount_total DECIMAL(15,0) NOT NULL DEFAULT 0,
    total DECIMAL(15,0) NOT NULL,
    deposit_amount DECIMAL(15,0) NULL,
    delivery_at DATETIME NULL,
    payment_info_json TEXT NULL,
    payment_reference VARCHAR(120) NULL,
    payment_note TEXT NULL,
    payment_verified_at DATETIME NULL,
    payment_verified_by BIGINT UNSIGNED NULL,
    cancelled_at DATETIME NULL,
    cancel_reason VARCHAR(500) NULL,
    refund_amount DECIMAL(15,0) NULL,
    refund_method ENUM('bank', 'cash') NULL,
    refund_note VARCHAR(500) NULL,
    refunded_at DATETIME NULL,
    refunded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_orders_user (user_id, created_at),
    INDEX idx_customer_orders_status (status, created_at),
    INDEX idx_customer_orders_payment_status (payment_verified_at, status),
    CONSTRAINT fk_customer_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_orders_verified_by FOREIGN KEY (payment_verified_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_orders_refunded_by FOREIGN KEY (refunded_by) REFERENCES users(id) ON DELETE SET NULL
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

CREATE TABLE IF NOT EXISTS customer_order_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(30) NULL,
    to_status VARCHAR(30) NOT NULL,
    action VARCHAR(30) NOT NULL,
    note VARCHAR(500) NULL,
    details_json TEXT NULL,
    actor_id BIGINT UNSIGNED NULL,
    actor_name VARCHAR(120) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_status_history_order (order_id, created_at),
    CONSTRAINT fk_order_status_history_order FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_status_history_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voucher_usages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    subtotal DECIMAL(15,0) NOT NULL,
    discount_amount DECIMAL(15,0) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_voucher_usages_voucher (voucher_id),
    INDEX idx_voucher_usages_user (voucher_id, user_id),
    INDEX idx_voucher_usages_order (order_id),
    CONSTRAINT fk_voucher_usages_voucher FOREIGN KEY (voucher_id) REFERENCES car_vouchers(id) ON DELETE CASCADE,
    CONSTRAINT fk_voucher_usages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_voucher_usages_order FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
