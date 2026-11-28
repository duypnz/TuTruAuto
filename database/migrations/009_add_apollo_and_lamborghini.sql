USE tu_tru_auto;

INSERT INTO cars
    (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT
    'Apollo Intensa Emozione (IE)', 'apollo-intensa-emozione', 'Apollo', 2019, 'Coupe', 'Xăng', 'Tuần tự Hewland 6 cấp', 1,
    'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Apollo_Intensa_Emozione_%282022%29_%2852566153941%29.jpg/1280px-Apollo_Intensa_Emozione_%282022%29_%2852566153941%29.jpg',
    JSON_ARRAY('https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Apollo_Intensa_Emozione_%282022%29_%2852566153941%29.jpg/1280px-Apollo_Intensa_Emozione_%282022%29_%2852566153941%29.jpg'),
    JSON_OBJECT(
        'price_on_request', TRUE,
        'specs', JSON_OBJECT('model_line','Intensa Emozione','category','Hypercar','engine','V12 hút khí tự nhiên, đặt giữa','displacement','6,3 lít','torque','760 Nm tại 6.000 vòng/phút','transmission','Tuần tự Hewland 6 cấp','fuel','Xăng','weight','1.250 kg','seats','2','doors','2','origin','Ý'),
        'variants', JSON_ARRAY(JSON_OBJECT('name','Intensa Emozione','price',0)),
        'colors', JSON_OBJECT('exterior',JSON_ARRAY('Tím','Vàng','Đen carbon'),'interior',JSON_ARRAY('Đen carbon')),
        'safety', JSON_ARRAY('Khung liền khối sợi carbon','Phanh hiệu năng cao'),
        'highlights', JSON_ARRAY('Sản xuất giới hạn 10 chiếc','Động cơ V12 hút khí tự nhiên','Đặt động cơ giữa, dẫn động cầu sau')
    ),
    'Siêu xe giới hạn · Báo giá theo xe',
    'Apollo Intensa Emozione là hypercar động cơ V12 đặt giữa, dẫn động cầu sau; hãng cho biết chỉ sản xuất 10 chiếc. Giá và tình trạng xe vui lòng liên hệ showroom để xác nhận.',
    1, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'apollo-intensa-emozione');

INSERT INTO cars
    (name, slug, brand, year, body_type, fuel, transmission, price, image_url, gallery_json, details_json, badge, description, featured, status, condition_type)
SELECT
    'Lamborghini Revuelto', 'lamborghini-revuelto', 'Lamborghini', 2025, 'Coupe', 'Hybrid', 'Ly hợp kép 8 cấp', 1,
    'https://images.unsplash.com/photo-1769632793201-d6ba433de10e?auto=format&fit=crop&w=1200&q=85',
    JSON_ARRAY('https://images.unsplash.com/photo-1769632793201-d6ba433de10e?auto=format&fit=crop&w=1200&q=85'),
    JSON_OBJECT(
        'price_on_request', TRUE,
        'specs', JSON_OBJECT('model_line','Revuelto','category','HPEV Hypercar','engine','V12 6,5 lít kết hợp 3 mô-tơ điện','displacement','6.498,5 cc','power','1.015 CV / 747 kW','transmission','Ly hợp kép 8 cấp','fuel','Hybrid xăng-điện','consumption','4,7 kWh/100 km + 15,0 l/100 km (WLTP)','acceleration','0–100 km/h trong 2,5 giây','top_speed','Trên 350 km/h','seats','2','doors','2','drive_type','Dẫn động 4 bánh','origin','Ý'),
        'variants', JSON_ARRAY(JSON_OBJECT('name','Revuelto','price',0)),
        'colors', JSON_OBJECT('exterior',JSON_ARRAY('Xanh Verde','Arancio','Giallo'),'interior',JSON_ARRAY('Nero Ade','Da cao cấp')),
        'safety', JSON_ARRAY('Khung liền khối sợi carbon','Phanh carbon-ceramic','Hệ thống dẫn động bốn bánh'),
        'comfort', JSON_ARRAY('Buồng lái kỹ thuật số','Màn hình riêng cho hành khách'),
        'highlights', JSON_ARRAY('Hệ truyền động HPEV V12 hybrid','Cánh gió sau chủ động','Ba mô-tơ điện')
    ),
    'Siêu xe HPEV · Báo giá theo cấu hình',
    'Lamborghini Revuelto kết hợp động cơ V12 6,5 lít với ba mô-tơ điện. Công suất kết hợp 1.015 CV, tăng tốc 0–100 km/h trong 2,5 giây và tốc độ tối đa trên 350 km/h theo thông số hãng. Giá và cấu hình vui lòng liên hệ showroom.',
    1, 'available', 'new'
WHERE NOT EXISTS (SELECT 1 FROM cars WHERE slug = 'lamborghini-revuelto');
