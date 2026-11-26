USE tu_tru_auto;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'status' AND COLUMN_TYPE NOT LIKE '%refunded%') > 0,
    'ALTER TABLE customer_orders MODIFY COLUMN status ENUM(''pending'', ''deposit_pending'', ''deposit_paid'', ''purchased'', ''cancelled'', ''refunded'') NOT NULL DEFAULT ''pending''',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'payment_reference') = 0,
    'ALTER TABLE customer_orders ADD COLUMN payment_reference VARCHAR(120) NULL AFTER payment_info_json',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'payment_note') = 0,
    'ALTER TABLE customer_orders ADD COLUMN payment_note TEXT NULL AFTER payment_reference',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'payment_verified_at') = 0,
    'ALTER TABLE customer_orders ADD COLUMN payment_verified_at DATETIME NULL AFTER payment_note',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'payment_verified_by') = 0,
    'ALTER TABLE customer_orders ADD COLUMN payment_verified_by BIGINT UNSIGNED NULL AFTER payment_verified_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'cancelled_at') = 0,
    'ALTER TABLE customer_orders ADD COLUMN cancelled_at DATETIME NULL AFTER payment_verified_by',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'cancel_reason') = 0,
    'ALTER TABLE customer_orders ADD COLUMN cancel_reason VARCHAR(500) NULL AFTER cancelled_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'refund_amount') = 0,
    'ALTER TABLE customer_orders ADD COLUMN refund_amount DECIMAL(15,0) NULL AFTER cancel_reason',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'refund_method') = 0,
    'ALTER TABLE customer_orders ADD COLUMN refund_method ENUM(''bank'', ''cash'') NULL AFTER refund_amount',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'refund_note') = 0,
    'ALTER TABLE customer_orders ADD COLUMN refund_note VARCHAR(500) NULL AFTER refund_method',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'refunded_at') = 0,
    'ALTER TABLE customer_orders ADD COLUMN refunded_at DATETIME NULL AFTER refund_note',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'refunded_by') = 0,
    'ALTER TABLE customer_orders ADD COLUMN refunded_by BIGINT UNSIGNED NULL AFTER refunded_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND CONSTRAINT_NAME = 'fk_customer_orders_verified_by' AND CONSTRAINT_TYPE = 'FOREIGN KEY') = 0,
    'ALTER TABLE customer_orders ADD CONSTRAINT fk_customer_orders_verified_by FOREIGN KEY (payment_verified_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND CONSTRAINT_NAME = 'fk_customer_orders_refunded_by' AND CONSTRAINT_TYPE = 'FOREIGN KEY') = 0,
    'ALTER TABLE customer_orders ADD CONSTRAINT fk_customer_orders_refunded_by FOREIGN KEY (refunded_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND INDEX_NAME = 'idx_customer_orders_payment_status') = 0,
    'ALTER TABLE customer_orders ADD INDEX idx_customer_orders_payment_status (payment_verified_at, status)',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

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
