USE tu_tru_auto;

ALTER TABLE car_inquiries
    MODIFY car_id BIGINT UNSIGNED NULL,
    MODIFY inquiry_type ENUM('buy', 'deposit', 'test_drive', 'quote', 'advice', 'contact') NOT NULL;

ALTER TABLE car_inquiries DROP FOREIGN KEY fk_car_inquiries_car;
ALTER TABLE car_inquiries
    ADD CONSTRAINT fk_car_inquiries_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_inquiries' AND COLUMN_NAME = 'email') = 0,
    'ALTER TABLE car_inquiries ADD COLUMN email VARCHAR(190) NULL AFTER phone',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_inquiries' AND COLUMN_NAME = 'inquiry_status') = 0,
    'ALTER TABLE car_inquiries ADD COLUMN inquiry_status ENUM(''new'', ''contacted'', ''closed'') NOT NULL DEFAULT ''new'' AFTER message',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_inquiries' AND INDEX_NAME = 'idx_car_inquiries_status') = 0,
    'ALTER TABLE car_inquiries ADD INDEX idx_car_inquiries_status (inquiry_status, created_at)',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_orders' AND COLUMN_NAME = 'payment_info_json') = 0,
    'ALTER TABLE customer_orders ADD COLUMN payment_info_json TEXT NULL AFTER delivery_at',
    'SELECT 1'
);
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

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

INSERT IGNORE INTO car_catalog_options (option_type, name) VALUES
('brand', 'Toyota'), ('brand', 'Mercedes-Benz'), ('brand', 'BMW'), ('brand', 'VinFast'),
('brand', 'Volvo'), ('brand', 'Lexus'), ('brand', 'Hyundai'), ('brand', 'Honda'),
('brand', 'Ford'), ('brand', 'Mazda'), ('brand', 'Porsche'),
('body_type', 'SUV'), ('body_type', 'Sedan'), ('body_type', 'MPV'),
('body_type', 'Coupe'), ('body_type', 'Hatchback'), ('body_type', 'Pickup');

UPDATE customer_orders
SET deposit_amount = ROUND(total * 0.10)
WHERE order_type = 'deposit' AND deposit_amount IS NULL;

UPDATE cars SET
    gallery_json = JSON_ARRAY(
        image_url,
        'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85'
    ),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'GLC', 'category', 'SUV', 'engine', 'I4 tăng áp, mild-hybrid 48V', 'displacement', '1.999 cc', 'power', '258 mã lực', 'torque', '400 Nm', 'transmission', 'Tự động 9 cấp 9G-TRONIC', 'fuel', 'Xăng', 'consumption', 'Khoảng 8,2 l/100 km (tham khảo)', 'dimensions', '4.716 x 1.890 x 1.640 mm', 'wheelbase', '2.888 mm', 'ground_clearance', 'Khoảng 165 mm', 'seats', '5', 'doors', '5', 'tank', '62 lít', 'trunk', '620 lít', 'origin', 'Đức'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 120000000), JSON_OBJECT('name', 'Luxury', 'price', 260000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc', 'Xanh'), 'interior', JSON_ARRAY('Đen', 'Nâu')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói hỗ trợ lái nâng cao', 'price', 45000000), JSON_OBJECT('name', 'Phủ ceramic', 'price', 18000000)),
        'safety', JSON_ARRAY('ABS, EBD, ESP', 'Hỗ trợ phanh chủ động', 'Cảnh báo điểm mù', 'Camera 360 độ', 'Hỗ trợ giữ làn'),
        'comfort', JSON_ARRAY('Màn hình giải trí trung tâm', 'Điều hòa tự động đa vùng', 'Cửa sổ trời toàn cảnh', 'Cốp điện', 'Ghế chỉnh điện'),
        'highlights', JSON_ARRAY('Hệ dẫn động 4MATIC', 'Hệ truyền động mild-hybrid 48V', 'Khoang cabin 5 chỗ rộng rãi'),
        'pros', JSON_ARRAY('Vận hành ổn định và cách âm tốt', 'Nhiều hệ thống hỗ trợ lái', 'Khoang hành lý linh hoạt'),
        'cons', JSON_ARRAY('Chi phí bảo dưỡng xe hạng sang cao hơn xe phổ thông', 'Mức tiêu hao thực tế tùy điều kiện vận hành')
    )
WHERE slug = 'mercedes-benz-glc-300' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', '5 Series', 'category', 'Sedan', 'engine', 'I4 tăng áp', 'displacement', '1.998 cc', 'power', '258 mã lực', 'torque', '400 Nm', 'transmission', 'Tự động 8 cấp Steptronic', 'fuel', 'Xăng', 'consumption', 'Khoảng 6,5-7,5 l/100 km (tham khảo)', 'dimensions', '5.060 x 1.900 x 1.515 mm', 'wheelbase', '2.995 mm', 'ground_clearance', 'Khoảng 140 mm', 'seats', '5', 'doors', '4', 'tank', '60 lít', 'trunk', '530 lít', 'origin', 'Đức'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 140000000), JSON_OBJECT('name', 'Luxury', 'price', 290000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Xám', 'Xanh'), 'interior', JSON_ARRAY('Đen', 'Nâu')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói hỗ trợ đỗ xe', 'price', 35000000)),
        'safety', JSON_ARRAY('Cân bằng điện tử', 'Kiểm soát hành trình', 'Cảnh báo va chạm', 'Camera lùi', 'Cảm biến trước sau'),
        'comfort', JSON_ARRAY('Ghế da chỉnh điện', 'Điều hòa tự động', 'Màn hình trung tâm', 'Cửa gió hàng ghế sau'),
        'highlights', JSON_ARRAY('Gói ngoại thất M Sport', 'Động cơ tăng áp 4 xi-lanh', 'Hệ thống treo tối ưu cho sedan'),
        'pros', JSON_ARRAY('Khả năng tăng tốc tốt', 'Khoang lái hướng về người lái', 'Cốp phù hợp nhu cầu gia đình'),
        'cons', JSON_ARRAY('Gầm thấp hơn SUV', 'Trang bị có thể khác theo xe thực tế')
    )
WHERE slug = 'bmw-530i-m-sport' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1568844293986-8d0400bd4745?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'XC60 Recharge', 'category', 'SUV', 'engine', 'I4 tăng áp kết hợp mô-tơ điện', 'displacement', '1.969 cc', 'power', '455 mã lực (hệ thống)', 'torque', '709 Nm (hệ thống)', 'transmission', 'Tự động 8 cấp', 'fuel', 'Plug-in Hybrid', 'consumption', 'Tùy điều kiện sạc và vận hành', 'dimensions', '4.708 x 1.902 x 1.653 mm', 'wheelbase', '2.865 mm', 'ground_clearance', 'Khoảng 216 mm', 'seats', '5', 'doors', '5', 'tank', '71 lít', 'trunk', '468 lít', 'origin', 'Thụy Điển'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 150000000), JSON_OBJECT('name', 'Luxury', 'price', 300000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc', 'Xanh'), 'interior', JSON_ARRAY('Đen', 'Be')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói hỗ trợ lái', 'price', 40000000)),
        'safety', JSON_ARRAY('City Safety', 'Hỗ trợ giữ làn', 'Cảnh báo phương tiện cắt ngang', 'Camera 360 độ', 'Hỗ trợ phanh khẩn cấp'),
        'comfort', JSON_ARRAY('Điều hòa tự động', 'Cửa sổ trời toàn cảnh', 'Ghế chỉnh điện', 'Màn hình giải trí cảm ứng'),
        'highlights', JSON_ARRAY('Hệ truyền động plug-in hybrid', 'Hỗ trợ sạc pin ngoài', 'Thiết kế Scandinavian'),
        'pros', JSON_ARRAY('Có thể di chuyển bằng điện trong quãng đường ngắn', 'Nhiều tính năng an toàn', 'Không gian 5 chỗ thực dụng'),
        'cons', JSON_ARRAY('Hiệu quả nhiên liệu phụ thuộc thói quen sạc', 'Cần kiểm tra lịch sử bảo dưỡng hệ thống hybrid')
    )
WHERE slug = 'volvo-xc60-recharge' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1611859266238-4b98091d9d9b?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'Macan', 'category', 'SUV', 'engine', 'I4 tăng áp', 'displacement', '1.984 cc', 'power', '265 mã lực', 'torque', '400 Nm', 'transmission', 'Ly hợp kép PDK 7 cấp', 'fuel', 'Xăng', 'consumption', 'Khoảng 8,5-9,5 l/100 km (tham khảo)', 'dimensions', '4.726 x 1.922 x 1.621 mm', 'wheelbase', '2.807 mm', 'ground_clearance', 'Khoảng 190 mm', 'seats', '5', 'doors', '5', 'tank', '65 lít', 'trunk', '488 lít', 'origin', 'Đức'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 200000000), JSON_OBJECT('name', 'Luxury', 'price', 420000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc', 'Đỏ'), 'interior', JSON_ARRAY('Đen', 'Be')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói thể thao', 'price', 55000000)),
        'safety', JSON_ARRAY('ABS, PSM', 'Kiểm soát hành trình', 'Cảnh báo áp suất lốp', 'Cảm biến đỗ xe'),
        'comfort', JSON_ARRAY('Điều hòa tự động', 'Ghế thể thao chỉnh điện', 'Màn hình cảm ứng', 'Cốp điện'),
        'highlights', JSON_ARRAY('Hệ dẫn động bốn bánh', 'Hộp số PDK', 'Tùy chọn cá nhân hóa đa dạng'),
        'pros', JSON_ARRAY('Cảm giác lái thể thao', 'Khả năng vận hành linh hoạt', 'Khoang hành lý dùng được hằng ngày'),
        'cons', JSON_ARRAY('Chi phí tùy chọn và bảo dưỡng cao', 'Hàng ghế sau phù hợp nhất với hai người lớn')
    )
WHERE slug = 'porsche-macan' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'VF 9 Plus', 'category', 'SUV', 'engine', 'Hai mô-tơ điện, dẫn động AWD', 'displacement', 'Không áp dụng (xe điện)', 'power', '402 mã lực', 'torque', '620 Nm', 'transmission', 'Hộp giảm tốc một cấp', 'fuel', 'Điện', 'consumption', 'Tùy điều kiện vận hành và sạc', 'dimensions', '5.118 x 2.254 x 1.696 mm (rộng gồm gương)', 'wheelbase', '3.150 mm', 'ground_clearance', 'Khoảng 197 mm', 'seats', '6 hoặc 7 tùy cấu hình', 'doors', '5', 'tank', 'Pin dung lượng khoảng 123 kWh', 'trunk', 'Thay đổi theo cấu hình ghế', 'origin', 'Việt Nam'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 100000000), JSON_OBJECT('name', 'Luxury', 'price', 220000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Đỏ', 'Xanh'), 'interior', JSON_ARRAY('Đen', 'Nâu')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Bộ sạc treo tường', 'price', 20000000)),
        'safety', JSON_ARRAY('Hỗ trợ lái nâng cao', 'Cảnh báo điểm mù', 'Hỗ trợ giữ làn', 'Camera 360 độ', 'Phanh khẩn cấp tự động'),
        'comfort', JSON_ARRAY('Màn hình giải trí trung tâm', 'Điều hòa đa vùng', 'Ghế chỉnh điện', 'Cửa sổ trời toàn cảnh'),
        'highlights', JSON_ARRAY('Hệ truyền động thuần điện', 'Dẫn động AWD', 'Cấu hình 6 hoặc 7 chỗ'),
        'pros', JSON_ARRAY('Tăng tốc tức thời', 'Không phát thải tại ống xả', 'Không gian cabin rộng'),
        'cons', JSON_ARRAY('Quãng đường thực tế phụ thuộc nhiệt độ và cách lái', 'Cần lên kế hoạch sạc cho hành trình dài')
    )
WHERE slug = 'vinfast-vf-9-plus' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'Alphard', 'category', 'MPV', 'engine', 'I4 tăng áp', 'displacement', '2.393 cc', 'power', '275 mã lực', 'torque', '430 Nm', 'transmission', 'Tự động 8 cấp', 'fuel', 'Xăng', 'consumption', 'Khoảng 9-11 l/100 km (tham khảo)', 'dimensions', '5.005 x 1.850 x 1.950 mm', 'wheelbase', '3.000 mm', 'ground_clearance', 'Khoảng 160 mm', 'seats', '7', 'doors', '5', 'tank', '65 lít', 'trunk', 'Thay đổi theo vị trí hàng ghế', 'origin', 'Nhật Bản'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 180000000), JSON_OBJECT('name', 'Luxury', 'price', 360000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc'), 'interior', JSON_ARRAY('Đen', 'Nâu')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói cách âm nâng cao', 'price', 25000000)),
        'safety', JSON_ARRAY('Toyota Safety Sense', 'Cảnh báo điểm mù', 'Hỗ trợ khởi hành ngang dốc', 'Camera 360 độ'),
        'comfort', JSON_ARRAY('Ghế thương gia chỉnh điện', 'Điều hòa nhiều vùng', 'Cửa trượt điện', 'Rèm che nắng hàng ghế sau'),
        'highlights', JSON_ARRAY('Bố trí 7 chỗ', 'Cửa trượt hai bên', 'Ưu tiên sự thoải mái hàng ghế sau'),
        'pros', JSON_ARRAY('Không gian hành khách rộng', 'Lên xuống xe thuận tiện', 'Phù hợp đưa đón gia đình và doanh nghiệp'),
        'cons', JSON_ARRAY('Kích thước lớn cần chú ý khi đỗ xe', 'Mức tiêu hao tùy tải trọng và giao thông')
    )
WHERE slug = 'toyota-alphard-luxury' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1606016159991-dfe4f2746ad5?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'RX 350', 'category', 'SUV', 'engine', 'I4 tăng áp', 'displacement', '2.393 cc', 'power', '275 mã lực', 'torque', '430 Nm', 'transmission', 'Tự động 8 cấp', 'fuel', 'Xăng', 'consumption', 'Khoảng 8,5-10 l/100 km (tham khảo)', 'dimensions', '4.890 x 1.920 x 1.695 mm', 'wheelbase', '2.850 mm', 'ground_clearance', 'Khoảng 205 mm', 'seats', '5', 'doors', '5', 'tank', '67 lít', 'trunk', '612 lít', 'origin', 'Nhật Bản'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 150000000), JSON_OBJECT('name', 'Luxury', 'price', 310000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc', 'Đỏ'), 'interior', JSON_ARRAY('Đen', 'Be')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Gói an toàn mở rộng', 'price', 35000000)),
        'safety', JSON_ARRAY('Lexus Safety System+', 'Cảnh báo điểm mù', 'Cảnh báo phương tiện cắt ngang', 'Camera 360 độ'),
        'comfort', JSON_ARRAY('Ghế da chỉnh điện', 'Điều hòa tự động', 'Cửa sổ trời', 'Cốp điện'),
        'highlights', JSON_ARRAY('Động cơ tăng áp 2.4L', 'Không gian 5 chỗ', 'Khoang cabin chú trọng cách âm'),
        'pros', JSON_ARRAY('Nội thất hoàn thiện cao', 'Tính năng hỗ trợ lái phong phú', 'Khoang hành lý rộng'),
        'cons', JSON_ARRAY('Chi phí sở hữu thuộc phân khúc cao', 'Mức tiêu hao tăng khi di chuyển nội đô')
    )
WHERE slug = 'lexus-rx-350-premium' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET
    gallery_json = JSON_ARRAY(image_url, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85'),
    details_json = JSON_OBJECT(
        'specs', JSON_OBJECT('model_line', 'Camry HEV', 'category', 'Sedan', 'engine', 'I4 kết hợp mô-tơ điện', 'displacement', '2.487 cc', 'power', 'Khoảng 184 mã lực (động cơ xăng)', 'torque', '221 Nm (động cơ xăng)', 'transmission', 'Hộp số e-CVT', 'fuel', 'Hybrid', 'consumption', 'Khoảng 4,2-5,0 l/100 km (tham khảo)', 'dimensions', '4.920 x 1.840 x 1.445 mm', 'wheelbase', '2.825 mm', 'ground_clearance', 'Khoảng 145 mm', 'seats', '5', 'doors', '4', 'tank', '50 lít', 'trunk', '524 lít', 'origin', 'Nhật Bản'),
        'variants', JSON_ARRAY(JSON_OBJECT('name', 'Base', 'price', 0), JSON_OBJECT('name', 'Premium', 'price', 70000000), JSON_OBJECT('name', 'Luxury', 'price', 140000000)),
        'colors', JSON_OBJECT('exterior', JSON_ARRAY('Trắng', 'Đen', 'Bạc', 'Đỏ'), 'interior', JSON_ARRAY('Đen', 'Be')),
        'options', JSON_ARRAY(JSON_OBJECT('name', 'Phim cách nhiệt', 'price', 12000000)),
        'safety', JSON_ARRAY('Toyota Safety Sense', 'Cảnh báo điểm mù', 'Cảnh báo phương tiện cắt ngang', 'Camera lùi'),
        'comfort', JSON_ARRAY('Điều hòa tự động', 'Ghế chỉnh điện', 'Màn hình giải trí', 'Cửa gió hàng ghế sau'),
        'highlights', JSON_ARRAY('Hệ truyền động hybrid', 'Hộp số e-CVT', 'Mức tiêu hao tham khảo thấp'),
        'pros', JSON_ARRAY('Vận hành êm khi đi phố', 'Tiết kiệm nhiên liệu theo điều kiện sử dụng', 'Khoang cabin 5 chỗ'),
        'cons', JSON_ARRAY('Dung tích cốp có thể khác bản xăng', 'Chi phí thay thế linh kiện hybrid cần được tư vấn')
    )
WHERE slug = 'toyota-camry-hev-premium' AND (details_json IS NULL OR details_json = '');

UPDATE cars SET description = 'Mercedes-Benz GLC 300 4MATIC là SUV 5 chỗ kết hợp động cơ tăng áp 2.0L và hệ dẫn động bốn bánh. Khoang cabin tập trung vào sự yên tĩnh, tiện nghi và các hệ thống hỗ trợ lái. Hàng ghế sau gập linh hoạt, phù hợp cả đi phố lẫn hành trình gia đình. Trang bị và thông số tiêu hao là dữ liệu tham khảo theo cấu hình.'
WHERE slug = 'mercedes-benz-glc-300' AND description = 'SUV hạng sang cân bằng giữa sự tinh tế, công nghệ an toàn và cảm giác lái đầy tự tin.';
UPDATE cars SET description = 'BMW 530i M Sport là sedan 5 Series với động cơ xăng tăng áp và hộp số tự động 8 cấp. Gói M Sport tạo điểm nhấn thể thao ở ngoại thất và khoang lái, trong khi trục cơ sở dài hỗ trợ sự thoải mái cho hành khách. Xe phù hợp người cần một mẫu sedan đường trường nhưng vẫn linh hoạt khi sử dụng hằng ngày.'
WHERE slug = 'bmw-530i-m-sport' AND description = 'Một chiếc sedan thể thao dành cho người yêu cảm giác cầm lái và thiết kế đậm chất riêng.';
UPDATE cars SET description = 'Volvo XC60 Recharge kết hợp động cơ xăng với mô-tơ điện trong hệ truyền động plug-in hybrid. Người dùng có thể sạc pin ngoài và lựa chọn chế độ vận hành phù hợp hành trình; hiệu quả nhiên liệu thực tế phụ thuộc mức sạc và thói quen sử dụng. Mẫu SUV 5 chỗ chú trọng các công nghệ an toàn chủ động và không gian tiện nghi.'
WHERE slug = 'volvo-xc60-recharge' AND description = 'Sự giao thoa của sức mạnh điện hóa, an toàn chuẩn Thụy Điển và không gian tiện nghi.';
UPDATE cars SET description = 'Porsche Macan là SUV thể thao 5 chỗ sử dụng động cơ xăng tăng áp và hộp số ly hợp kép PDK. Thiết kế gầm và hệ dẫn động hướng đến cảm giác lái linh hoạt, đồng thời vẫn có khoang hành lý đáp ứng nhu cầu sử dụng thường ngày. Chi phí bảo dưỡng, trang bị tùy chọn và cấu hình xe cần được xác nhận theo xe thực tế.'
WHERE slug = 'porsche-macan' AND description = 'Chiếc SUV thể thao linh hoạt trong phố và đầy phấn khích trên những cung đường dài.';
UPDATE cars SET description = 'VinFast VF 9 Plus là SUV điện cỡ lớn với hai mô-tơ điện và hệ dẫn động AWD. Khoang xe có cấu hình 6 hoặc 7 chỗ tùy phiên bản; quãng đường di chuyển phụ thuộc tình trạng pin, nhiệt độ, tốc độ và tải trọng. Khách hàng nên xác nhận cấu hình pin, chính sách sạc và bảo hành áp dụng cho chiếc xe cụ thể.'
WHERE slug = 'vinfast-vf-9-plus' AND description = 'SUV điện cỡ lớn với không gian rộng rãi, vận hành êm ái và công nghệ thông minh.';
UPDATE cars SET description = 'Toyota Alphard Luxury là MPV 7 chỗ thiết kế ưu tiên sự thoải mái của hành khách, đặc biệt ở hàng ghế sau. Cửa trượt điện và cách bố trí cabin giúp lên xuống xe thuận tiện; kích thước lớn phù hợp đi đường dài và đưa đón. Trang bị, số chỗ và thông số động cơ cần đối chiếu với hồ sơ của xe đang bán.'
WHERE slug = 'toyota-alphard-luxury' AND description = 'Không gian thương gia rộng rãi, nâng tầm sự thoải mái cho mỗi hành khách.';
UPDATE cars SET description = 'Lexus RX 350 Premium là SUV 5 chỗ dùng động cơ xăng tăng áp 2.4L và hộp số tự động 8 cấp. Cabin chú trọng vật liệu hoàn thiện, độ êm và tiện ích cho người lái; hệ thống hỗ trợ an toàn tùy thuộc phiên bản. Đây là lựa chọn dành cho khách cần không gian SUV cao cấp nhưng vẫn sử dụng linh hoạt hằng ngày.'
WHERE slug = 'lexus-rx-350-premium' AND description = 'SUV hạng sang với cabin yên tĩnh, chất liệu cao cấp và độ hoàn thiện tỉ mỉ.';
UPDATE cars SET description = 'Toyota Camry HEV Premium kết hợp động cơ xăng 2.5L với mô-tơ điện và hộp số e-CVT. Hệ thống hybrid tối ưu việc chuyển đổi giữa hai nguồn động lực, đặc biệt hữu ích khi di chuyển thường xuyên trong đô thị. Sedan 5 chỗ có khoang hành khách rộng và các trang bị hỗ trợ lái; mức tiêu hao thực tế tùy điều kiện vận hành.'
WHERE slug = 'toyota-camry-hev-premium' AND description = 'Sedan hybrid hiện đại mang đến khả năng vận hành êm ái và hiệu quả nhiên liệu vượt trội.';

INSERT INTO cars (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT 'Ford Ranger Wildtrak 2.0 Bi-Turbo', 'ford-ranger-wildtrak-2025', 'Ford', 2025, 'Pickup', 'Dầu', 'Tự động 10 cấp', 965000000,
       'https://images.unsplash.com/photo-1551830820-330a71b99659?auto=format&fit=crop&w=1200&q=85',
       JSON_ARRAY('https://images.unsplash.com/photo-1551830820-330a71b99659?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1518987048-93e29699e79a?auto=format&fit=crop&w=1200&q=85'),
       JSON_OBJECT('specs', JSON_OBJECT('model_line','Ranger','category','Pickup','engine','I4 Bi-Turbo tăng áp kép','displacement','1.996 cc','power','210 mã lực','torque','500 Nm','transmission','Tự động 10 cấp','fuel','Dầu','consumption','Theo điều kiện tải và vận hành','dimensions','5.370 x 1.918 x 1.884 mm','wheelbase','3.270 mm','ground_clearance','Khoảng 235 mm','seats','5','doors','4','tank','80 lít','trunk','Thùng hàng pickup','origin','Hoa Kỳ'),'variants',JSON_ARRAY(JSON_OBJECT('name','Base','price',0),JSON_OBJECT('name','Premium','price',55000000),JSON_OBJECT('name','Luxury','price',105000000)),'colors',JSON_OBJECT('exterior',JSON_ARRAY('Trắng','Đen','Xám','Cam'),'interior',JSON_ARRAY('Đen')),'options',JSON_ARRAY(JSON_OBJECT('name','Nắp thùng thấp','price',18000000)),'safety',JSON_ARRAY('Cân bằng điện tử','Hỗ trợ đổ đèo','Camera lùi','Cảm biến đỗ xe'),'comfort',JSON_ARRAY('Màn hình giải trí','Điều hòa tự động','Ghế chỉnh điện'),'highlights',JSON_ARRAY('Động cơ diesel Bi-Turbo','Khung gầm rời','Thùng hàng đa dụng'),'pros',JSON_ARRAY('Khả năng chở hàng linh hoạt','Khoảng sáng gầm cao','Phù hợp đường trường'),'cons',JSON_ARRAY('Kích thước lớn khi đi phố','Động cơ diesel có tiếng ồn đặc trưng')),
       'Pickup đa dụng', 'Xe bán tải 2.0 Bi-Turbo phục vụ cả nhu cầu đi lại và chuyên chở. Thông số tham khảo theo cấu hình; vui lòng đối chiếu giấy tờ xe thực tế.', 1, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'ford-ranger-wildtrak-2025');

INSERT INTO cars (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT 'Mazda3 Sport 1.5 Luxury', 'mazda3-sport-15-luxury', 'Mazda', 2025, 'Hatchback', 'Xăng', 'Tự động 6 cấp', 699000000,
       'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85',
       JSON_ARRAY('https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1611859266238-4b98091d9d9b?auto=format&fit=crop&w=1200&q=85'),
       JSON_OBJECT('specs', JSON_OBJECT('model_line','Mazda3 Sport','category','Hatchback','engine','I4 Skyactiv-G','displacement','1.496 cc','power','110 mã lực','torque','146 Nm','transmission','Tự động 6 cấp','fuel','Xăng','consumption','Khoảng 6,5-7,5 l/100 km (tham khảo)','dimensions','4.460 x 1.795 x 1.435 mm','wheelbase','2.725 mm','ground_clearance','Khoảng 145 mm','seats','5','doors','5','tank','51 lít','trunk','334 lít','origin','Nhật Bản'),'variants',JSON_ARRAY(JSON_OBJECT('name','Base','price',0),JSON_OBJECT('name','Premium','price',45000000),JSON_OBJECT('name','Luxury','price',90000000)),'colors',JSON_OBJECT('exterior',JSON_ARRAY('Trắng','Đen','Đỏ','Xám'),'interior',JSON_ARRAY('Đen','Nâu')),'options',JSON_ARRAY(JSON_OBJECT('name','Phim cách nhiệt','price',10000000)),'safety',JSON_ARRAY('Cân bằng điện tử','Hỗ trợ phanh','Camera lùi','Cảm biến đỗ xe'),'comfort',JSON_ARRAY('Màn hình trung tâm','Điều hòa tự động','Ghế chỉnh điện'),'highlights',JSON_ARRAY('Thiết kế hatchback thể thao','Động cơ xăng hút khí tự nhiên','Kích thước gọn gàng'),'pros',JSON_ARRAY('Dễ xoay trở trong đô thị','Khoang hành lý mở rộng khi gập ghế','Thiết kế trẻ trung'),'cons',JSON_ARRAY('Hàng ghế sau phù hợp nhất với hai người lớn','Khoảng sáng gầm vừa phải')),
       'Hatchback đô thị', 'Hatchback 5 cửa hướng đến nhu cầu di chuyển linh hoạt trong đô thị. Mức tiêu hao và trang bị có thể thay đổi theo phiên bản.', 0, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'mazda3-sport-15-luxury');

INSERT INTO cars (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT 'Honda CR-V e:HEV RS', 'honda-cr-v-ehev-rs', 'Honda', 2025, 'SUV', 'Hybrid', 'e-CVT', 1259000000,
       'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85',
       JSON_ARRAY('https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1568844293986-8d0400bd4745?auto=format&fit=crop&w=1200&q=85'),
       JSON_OBJECT('specs', JSON_OBJECT('model_line','CR-V e:HEV','category','SUV','engine','I4 kết hợp mô-tơ điện','displacement','1.993 cc','power','Khoảng 204 mã lực (hệ thống)','torque','335 Nm (mô-tơ điện)','transmission','e-CVT','fuel','Hybrid','consumption','Khoảng 5,0-6,5 l/100 km (tham khảo)','dimensions','4.706 x 1.866 x 1.681 mm','wheelbase','2.700 mm','ground_clearance','Khoảng 198 mm','seats','5','doors','5','tank','57 lít','trunk','Thay đổi theo cấu hình ghế','origin','Nhật Bản'),'variants',JSON_ARRAY(JSON_OBJECT('name','Base','price',0),JSON_OBJECT('name','Premium','price',80000000),JSON_OBJECT('name','Luxury','price',160000000)),'colors',JSON_OBJECT('exterior',JSON_ARRAY('Trắng','Đen','Bạc','Đỏ'),'interior',JSON_ARRAY('Đen')),'options',JSON_ARRAY(JSON_OBJECT('name','Gói phụ kiện bảo vệ','price',15000000)),'safety',JSON_ARRAY('Honda Sensing','Hỗ trợ giữ làn','Phanh giảm thiểu va chạm','Camera đa góc nhìn'),'comfort',JSON_ARRAY('Điều hòa tự động','Cửa sổ trời','Ghế chỉnh điện','Màn hình giải trí'),'highlights',JSON_ARRAY('Hệ truyền động hybrid','Khoang cabin đa dụng','Gói an toàn chủ động'),'pros',JSON_ARRAY('Vận hành êm ái','Không gian linh hoạt','Hệ thống hỗ trợ lái phong phú'),'cons',JSON_ARRAY('Giá và trang bị khác nhau theo phiên bản','Hiệu quả nhiên liệu phụ thuộc điều kiện sử dụng')),
       'Hybrid tiết kiệm', 'SUV hybrid 5 chỗ hướng đến gia đình, kết hợp động cơ xăng và mô-tơ điện. Mức tiêu hao là số tham khảo, phụ thuộc điều kiện sử dụng.', 0, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'honda-cr-v-ehev-rs');

INSERT INTO cars (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT 'Porsche 718 Cayman', 'porsche-718-cayman-2025', 'Porsche', 2025, 'Coupe', 'Xăng', 'Ly hợp kép 7 cấp', 3990000000,
       'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85',
       JSON_ARRAY('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1611859266238-4b98091d9d9b?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85'),
       JSON_OBJECT('specs', JSON_OBJECT('model_line','718 Cayman','category','Coupe','engine','I4 tăng áp','displacement','1.988 cc','power','300 mã lực','torque','380 Nm','transmission','Ly hợp kép PDK 7 cấp','fuel','Xăng','consumption','Khoảng 8,5-10 l/100 km (tham khảo)','dimensions','4.379 x 1.801 x 1.295 mm','wheelbase','2.475 mm','ground_clearance','Khoảng 120 mm','seats','2','doors','2','tank','64 lít','trunk','Hai khoang hành lý, tổng khoảng 275 lít','origin','Đức'),'variants',JSON_ARRAY(JSON_OBJECT('name','Base','price',0),JSON_OBJECT('name','Premium','price',250000000),JSON_OBJECT('name','Luxury','price',500000000)),'colors',JSON_OBJECT('exterior',JSON_ARRAY('Trắng','Đen','Bạc','Đỏ'),'interior',JSON_ARRAY('Đen','Đỏ')),'options',JSON_ARRAY(JSON_OBJECT('name','Gói thể thao','price',65000000)),'safety',JSON_ARRAY('Cân bằng ổn định điện tử','Hỗ trợ phanh','Cảm biến áp suất lốp'),'comfort',JSON_ARRAY('Ghế thể thao','Điều hòa tự động','Màn hình giải trí'),'highlights',JSON_ARRAY('Kiểu dáng coupe 2 cửa','Động cơ đặt giữa','Hộp số PDK'),'pros',JSON_ARRAY('Phản hồi lái thể thao','Kích thước gọn','Thiết kế đặc trưng'),'cons',JSON_ARRAY('Chỉ có hai chỗ ngồi','Khoảng sáng gầm thấp và chi phí sử dụng cao')),
       'Coupe thể thao', 'Coupe hai chỗ với bố trí động cơ đặt giữa, phù hợp khách hàng ưu tiên trải nghiệm lái. Thông số tham khảo cần đối chiếu cấu hình xe thực tế.', 0, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'porsche-718-cayman-2025');
