SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'email') = 0,
    'ALTER TABLE customer_orders ADD COLUMN email VARCHAR(190) NULL AFTER phone',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
