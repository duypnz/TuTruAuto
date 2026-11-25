USE tu_tru_auto;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'deposit_amount') = 0,
    'ALTER TABLE customer_orders ADD COLUMN deposit_amount DECIMAL(15,0) NULL AFTER total',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'delivery_at') = 0,
    'ALTER TABLE customer_orders ADD COLUMN delivery_at DATETIME NULL AFTER deposit_amount',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
