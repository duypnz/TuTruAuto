USE tu_tru_auto;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'name') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN name VARCHAR(120) NULL AFTER code',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'usage_limit') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN usage_limit INT UNSIGNED NULL AFTER is_active',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'usage_per_user') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN usage_per_user INT UNSIGNED NULL AFTER usage_limit',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'used_count') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN used_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER usage_per_user',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'max_discount') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN max_discount DECIMAL(15,0) NULL AFTER minimum_order',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'scope_type') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN scope_type ENUM(''all'', ''brand'', ''car'') NOT NULL DEFAULT ''all'' AFTER max_discount',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'scope_value') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN scope_value VARCHAR(190) NULL AFTER scope_type',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'user_id') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER scope_value',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'deleted_at') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN deleted_at DATETIME NULL AFTER user_id',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'created_at') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER deleted_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND COLUMN_NAME = 'updated_at') = 0,
    'ALTER TABLE car_vouchers ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND INDEX_NAME = 'idx_car_vouchers_lookup') = 0,
    'ALTER TABLE car_vouchers ADD INDEX idx_car_vouchers_lookup (is_active, deleted_at, ends_at)',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_vouchers' AND CONSTRAINT_NAME = 'fk_car_vouchers_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY') = 0,
    'ALTER TABLE car_vouchers ADD CONSTRAINT fk_car_vouchers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

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

UPDATE car_vouchers
SET name = IF(name IS NULL OR name = '', 'Giảm 5% cho đơn từ 500 triệu đồng', name),
    max_discount = IF(max_discount IS NULL, 100000000, max_discount),
    scope_type = 'all'
WHERE code = 'TUTRU5';
