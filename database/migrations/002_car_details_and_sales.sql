USE tu_tru_auto;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'condition_type') = 0,
    'ALTER TABLE cars ADD COLUMN condition_type ENUM(''new'', ''used'') NOT NULL DEFAULT ''new'' AFTER status',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'discount_price') = 0,
    'ALTER TABLE cars ADD COLUMN discount_price DECIMAL(15,0) NULL AFTER price',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'car_code') = 0,
    'ALTER TABLE cars ADD COLUMN car_code VARCHAR(40) NULL AFTER slug',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'gallery_json') = 0,
    'ALTER TABLE cars ADD COLUMN gallery_json LONGTEXT NULL AFTER image_url',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'video_url') = 0,
    'ALTER TABLE cars ADD COLUMN video_url VARCHAR(1000) NULL AFTER gallery_json',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'details_json') = 0,
    'ALTER TABLE cars ADD COLUMN details_json LONGTEXT NULL AFTER video_url',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

UPDATE cars SET car_code = CONCAT('TTA-', LPAD(id, 6, '0')) WHERE car_code IS NULL OR car_code = '';

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND INDEX_NAME = 'uq_cars_car_code') = 0,
    'ALTER TABLE cars ADD UNIQUE INDEX uq_cars_car_code (car_code)',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

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

INSERT INTO car_vouchers (code, discount_type, discount_value, minimum_order, starts_at, ends_at, is_active)
VALUES ('TUTRU5', 'percent', 5, 500000000, '2020-01-01 00:00:00', '2035-12-31 23:59:59', 1)
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active);

CREATE TABLE IF NOT EXISTS car_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    inquiry_type ENUM('buy', 'deposit', 'test_drive', 'quote', 'advice') NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_car_inquiries_created (created_at),
    CONSTRAINT fk_car_inquiries_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_car_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
