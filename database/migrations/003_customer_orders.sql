USE tu_tru_auto;

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
