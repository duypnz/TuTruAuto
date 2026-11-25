USE tu_tru_auto;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'stock_quantity') = 0,
    'ALTER TABLE cars ADD COLUMN stock_quantity INT UNSIGNED NULL DEFAULT NULL AFTER discount_price',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'stock_quantity') = 'NO',
    'ALTER TABLE cars MODIFY COLUMN stock_quantity INT UNSIGNED NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'lead_time_days') = 0,
    'ALTER TABLE cars ADD COLUMN lead_time_days SMALLINT UNSIGNED NOT NULL DEFAULT 21 AFTER stock_quantity',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
