USE tu_tru_auto;

UPDATE cars SET condition_type = 'new';

UPDATE cars SET gallery_json = JSON_ARRAY(
    image_url,
    'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85',
    'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85'
) WHERE slug = 'mercedes-benz-glc-300';

UPDATE cars SET gallery_json = JSON_ARRAY(
    image_url,
    'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85',
    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85'
) WHERE slug = 'bmw-530i-m-sport';

UPDATE cars SET gallery_json = JSON_ARRAY(
    image_url,
    'https://images.unsplash.com/photo-1568844293986-8d0400bd4745?auto=format&fit=crop&w=1200&q=85',
    'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85'
) WHERE slug = 'volvo-xc60-recharge';

UPDATE cars SET gallery_json = JSON_ARRAY(
    image_url,
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85',
    'https://images.unsplash.com/photo-1611859266238-4b98091d9d9b?auto=format&fit=crop&w=1200&q=85'
) WHERE slug = 'porsche-macan';

INSERT IGNORE INTO cars (name,slug,brand,year,body_type,fuel,transmission,price,image_url,badge,description,featured,status,condition_type,gallery_json,details_json) VALUES
('Honda CR-V L','honda-cr-v-l','Honda',2025,'SUV','Hybrid','CVT',1200000000,'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=85','Xe mới','SUV 5 chỗ với khoang cabin rộng, vận hành êm ái và công nghệ hỗ trợ lái Honda Sensing.',1,'available','new',JSON_ARRAY('https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=85'),JSON_OBJECT('specs',JSON_OBJECT('engine','I4 hybrid','displacement','1.993 cc','power','181 mã lực','torque','335 Nm','transmission','CVT','fuel','Hybrid','consumption','Khoảng 6,5-7,5 l/100 km','seats','5','doors','5','drive_type','Dẫn động cầu trước'))),
('Mazda CX-5 Premium','mazda-cx-5-premium','Mazda',2025,'SUV','Xăng','Tự động 6 cấp',1050000000,'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=1200&q=85','Ưu đãi tháng','SUV cỡ trung thể thao, thiết kế KODO tinh tế và cảm giác lái cân đối.',1,'available','new',JSON_ARRAY('https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85'),JSON_OBJECT('specs',JSON_OBJECT('engine','I4 tăng áp','displacement','2.488 cc','power','187 mã lực','torque','250 Nm','transmission','Tự động 6 cấp','fuel','Xăng','consumption','Khoảng 8-9 l/100 km','seats','5','doors','5','drive_type','Dẫn động cầu trước'))),
('Ford Everest Titanium','ford-everest-titanium','Ford',2025,'SUV','Dầu','Tự động 10 cấp',1550000000,'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=85','7 chỗ','SUV 7 chỗ vận hành mạnh mẽ, gầm cao và linh hoạt cho gia đình hoặc địa hình hỗn hợp.',1,'available','new',JSON_ARRAY('https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1200&q=85'),JSON_OBJECT('specs',JSON_OBJECT('engine','I4 Diesel tăng áp','displacement','2.993 cc','power','250 mã lực','torque','600 Nm','transmission','Tự động 10 cấp','fuel','Dầu','consumption','Khoảng 8-10 l/100 km','seats','7','doors','5','drive_type','Dẫn động 4 bánh'))),
('Hyundai Tucson 2.0','hyundai-tucson-2.0','Hyundai',2025,'SUV','Xăng','Tự động 6 cấp',820000000,'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1200&q=85','Cỡ hợp lý','SUV 5 chỗ thiết kế sắc sảo, sử dụng dễ dàng trong phố và trang bị tiện nghi cho chuyến đi dài.',0,'available','new',JSON_ARRAY('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85','https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=1200&q=85'),JSON_OBJECT('specs',JSON_OBJECT('engine','I4 xăng','displacement','1.998 cc','power','156 mã lực','torque','192 Nm','transmission','Tự động 6 cấp','fuel','Xăng','consumption','Khoảng 8-9 l/100 km','seats','5','doors','5','drive_type','Dẫn động cầu trước')));
