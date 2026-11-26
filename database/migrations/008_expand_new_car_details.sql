USE tu_tru_auto;

UPDATE cars
SET fuel = 'Xăng',
    transmission = 'CVT',
    description = 'Honda CR-V L là SUV cỡ trung hướng đến gia đình cần không gian linh hoạt cho đi phố và những chuyến đi dài. Cấu hình động cơ xăng tăng áp 1.5L kết hợp hộp số CVT ưu tiên khả năng vận hành liền mạch trong điều kiện giao thông thường ngày. Hàng ghế và khoang hành lý có thể sắp xếp theo nhu cầu chở người hoặc hành lý; hãy kiểm tra cấu hình ghế của xe thực tế. Khi lái thử, nên đánh giá tầm nhìn, độ êm, khả năng xoay trở và mức độ phù hợp với tuyến đường thường đi. Thông số và trang bị có thể khác theo năm sản xuất, thị trường và phiên bản; showroom sẽ đối chiếu giấy tờ, cấu hình cùng chính sách bảo hành trước khi xác nhận giao dịch.',
    details_json = JSON_SET(
        CASE WHEN JSON_VALID(details_json) THEN details_json ELSE '{}' END,
        '$.specs.engine', 'I4 tăng áp, phun xăng trực tiếp',
        '$.specs.displacement', '1.498 cc',
        '$.specs.power', '188 mã lực (tham khảo)',
        '$.specs.torque', '240 Nm (tham khảo)',
        '$.specs.fuel', 'Xăng',
        '$.specs.transmission', 'CVT',
        '$.specs.seats', '5 hoặc 7 tùy cấu hình',
        '$.safety', JSON_ARRAY('Honda Sensing (đối chiếu theo xe thực tế)', 'Hỗ trợ phanh và cân bằng thân xe (tùy phiên bản)', 'Camera và cảm biến hỗ trợ đỗ xe (tùy phiên bản)', 'Túi khí và hệ thống an toàn thụ động (kiểm tra cấu hình xe)'),
        '$.comfort', JSON_ARRAY('Điều hòa và cửa gió cho hành khách (tùy phiên bản)', 'Màn hình giải trí và kết nối điện thoại (đối chiếu theo xe)', 'Ghế chỉnh điện và tiện ích cabin (tùy phiên bản)', 'Hàng ghế sau gập linh hoạt để tăng không gian hành lý'),
        '$.highlights', JSON_ARRAY('Động cơ xăng tăng áp 1.5L kết hợp hộp số CVT', 'Kiểu dáng SUV phù hợp nhu cầu gia đình', 'Có lựa chọn cấu hình ghế theo phiên bản', 'Khoang hành lý có thể thay đổi theo cách bố trí ghế', 'Nên lái thử để kiểm tra tầm nhìn và độ êm trên cung đường quen thuộc'),
        '$.pros', JSON_ARRAY('Kiểu thân xe gầm cao giúp việc lên xuống xe thuận tiện hơn', 'Hộp số CVT hướng đến khả năng vận hành liền mạch trong phố', 'Không gian cabin có thể bố trí theo nhu cầu chở người hoặc hành lý', 'Các gói hỗ trợ lái cần được đối chiếu theo trang bị xe cụ thể', 'Phù hợp khách hàng cần một mẫu xe đa dụng cho nhiều mục đích'),
        '$.cons', JSON_ARRAY('Kích thước SUV cần được cân nhắc nếu thường xuyên đỗ trong không gian hẹp', 'Mức tiêu hao thực tế thay đổi theo tải trọng và tình trạng giao thông', 'Cấu hình 5 hoặc 7 chỗ và trang bị tiện nghi có thể khác theo phiên bản', 'Cần xác nhận xe đang bán là cấu hình nào trước khi so sánh giá', 'Phí lăn bánh và bảo hiểm chưa nằm trong giá niêm yết')
    )
WHERE slug = 'honda-cr-v-l';

UPDATE cars
SET description = 'Mazda CX-5 Premium là SUV cỡ trung với thiết kế KODO đặc trưng và khoang cabin hướng đến trải nghiệm sử dụng gọn gàng, dễ làm quen. Động cơ xăng Skyactiv-G 2.5L hút khí tự nhiên kết hợp hộp số tự động 6 cấp tạo nên cấu hình phù hợp cả đi lại hằng ngày lẫn hành trình ngoài đô thị. Hàng ghế sau gập được để mở rộng khoang hành lý; khách hàng nên thử trực tiếp không gian đầu gối, tầm nhìn và độ thoải mái của ghế. Khi lái thử, có thể chú ý độ phản hồi chân ga, vô-lăng và khả năng cách âm trên mặt đường quen thuộc. Trang bị an toàn, tiện nghi, mức tiêu hao thực tế và giá có thể thay đổi theo phiên bản/năm sản xuất; hãy xác nhận cấu hình và danh sách phụ kiện đúng với xe đang được chào bán.',
    details_json = JSON_SET(
        CASE WHEN JSON_VALID(details_json) THEN details_json ELSE '{}' END,
        '$.specs.engine', 'I4 Skyactiv-G hút khí tự nhiên',
        '$.specs.displacement', '2.488 cc',
        '$.specs.power', '187 mã lực (tham khảo)',
        '$.specs.torque', '252 Nm (tham khảo)',
        '$.safety', JSON_ARRAY('Hệ thống chống bó cứng phanh và cân bằng điện tử (đối chiếu theo phiên bản)', 'Hỗ trợ phanh và kiểm soát lực kéo (tùy phiên bản)', 'Camera lùi và cảm biến đỗ xe (kiểm tra theo xe thực tế)', 'Các tính năng i-Activsense có thể khác theo cấu hình'),
        '$.comfort', JSON_ARRAY('Ghế và vô-lăng điều chỉnh theo trang bị từng phiên bản', 'Màn hình trung tâm và kết nối giải trí (đối chiếu theo xe)', 'Điều hòa tự động (tùy phiên bản)', 'Hàng ghế sau gập để mở rộng khoang hành lý'),
        '$.highlights', JSON_ARRAY('Động cơ Skyactiv-G 2.5L hút khí tự nhiên', 'Hộp số tự động 6 cấp', 'Thiết kế ngoại thất theo ngôn ngữ KODO', 'Khoang hành lý có thể mở rộng bằng cách gập hàng ghế sau', 'Kích thước phù hợp nhu cầu sử dụng gia đình'),
        '$.pros', JSON_ARRAY('Kiểu dáng SUV gầm cao, thuận tiện cho tầm quan sát', 'Động cơ hút khí tự nhiên có cấu hình dễ hiểu và quen thuộc', 'Hộp số tự động 6 cấp phù hợp sử dụng hỗn hợp', 'Hàng ghế sau gập hỗ trợ chở vật dụng dài', 'Có thể đánh giá trực tiếp độ thoải mái và cách âm khi lái thử'),
        '$.cons', JSON_ARRAY('Mức tiêu hao phụ thuộc nhiều vào quãng đường đô thị và tải trọng', 'Trang bị an toàn chủ động không đồng nhất giữa các phiên bản', 'Kích thước xe cần được cân nhắc khi đỗ ở hầm hoặc ngõ hẹp', 'Cần xác minh đúng động cơ và gói trang bị trước khi ký hợp đồng', 'Chi phí lăn bánh và bảo hiểm thay đổi theo địa phương')
    )
WHERE slug = 'mazda-cx-5-premium';

UPDATE cars
SET description = 'Ford Everest Titanium là SUV 7 chỗ sử dụng động cơ diesel 2.0L Bi-Turbo và hộp số tự động 10 cấp theo cấu hình tham khảo. Thân xe gầm cao cùng khoang cabin nhiều hàng ghế phù hợp gia đình đông người, đồng thời đáp ứng nhu cầu đi đường dài. Trước khi chọn xe, nên thử lên xuống hàng ghế cuối, kiểm tra không gian hành lý khi đủ người và đánh giá kích thước xe tại nơi thường đỗ. Khi lái thử, có thể kiểm tra độ êm ở tốc độ thấp, khả năng quan sát và cảm giác vận hành khi đủ tải. Trang bị hỗ trợ lái, hệ dẫn động, mức tiêu hao và cấu hình động cơ cần được xác nhận theo đúng năm sản xuất/phiên bản ghi trên giấy tờ.',
    details_json = JSON_SET(
        CASE WHEN JSON_VALID(details_json) THEN details_json ELSE '{}' END,
        '$.specs.engine', 'I4 Diesel 2.0L Bi-Turbo',
        '$.specs.displacement', '1.996 cc',
        '$.specs.power', '210 mã lực (tham khảo)',
        '$.specs.torque', '500 Nm (tham khảo)',
        '$.specs.seats', '7',
        '$.safety', JSON_ARRAY('Ford Co-Pilot360 (đối chiếu gói trang bị thực tế)', 'Hỗ trợ phanh và cân bằng điện tử (tùy phiên bản)', 'Hỗ trợ đổ đèo (kiểm tra theo cấu hình)', 'Camera và cảm biến hỗ trợ đỗ xe (tùy phiên bản)'),
        '$.comfort', JSON_ARRAY('Bố trí cabin 7 chỗ', 'Điều hòa cho nhiều hàng ghế (đối chiếu theo xe)', 'Màn hình giải trí và kết nối (tùy phiên bản)', 'Hàng ghế có thể gập để tăng không gian chở đồ'),
        '$.highlights', JSON_ARRAY('Động cơ diesel 2.0L Bi-Turbo theo cấu hình tham khảo', 'Hộp số tự động 10 cấp', 'Bố trí 7 chỗ cho gia đình đông người', 'Khoảng sáng gầm phù hợp đường hỗn hợp', 'Kiểm tra hệ dẫn động và gói hỗ trợ lái theo đúng phiên bản'),
        '$.pros', JSON_ARRAY('Bố trí 7 chỗ hỗ trợ chở nhiều hành khách', 'Thân xe gầm cao phù hợp nhiều điều kiện đường sá', 'Động cơ diesel tăng áp hướng đến khả năng kéo tải', 'Khoang hành lý thay đổi được khi gập hàng ghế', 'Có thể kiểm tra khả năng vận hành đủ tải qua buổi lái thử'),
        '$.cons', JSON_ARRAY('Kích thước lớn cần chú ý khi di chuyển và đỗ xe trong đô thị', 'Động cơ diesel có thể ồn/rung hơn xe xăng ở tốc độ thấp', 'Mức tiêu hao tăng khi chở đủ tải hoặc đi đường đông', 'Trang bị và hệ dẫn động phụ thuộc cấu hình cụ thể', 'Chi phí lăn bánh, nhiên liệu và bảo dưỡng cần được tính trước')
    )
WHERE slug = 'ford-everest-titanium';

UPDATE cars
SET description = 'Hyundai Tucson 2.0 là SUV 5 chỗ có kích thước phù hợp nhu cầu gia đình và sử dụng hằng ngày. Động cơ xăng 2.0L kết hợp hộp số tự động 6 cấp tạo ra cấu hình quen thuộc, dễ tiếp cận với người lái chuyển từ sedan sang xe gầm cao. Khoang cabin và hành lý nên được kiểm tra trực tiếp theo số người thường xuyên di chuyển; khi lái thử, hãy chú ý tầm nhìn, độ êm và khả năng xoay trở ở nơi chật. Các tính năng an toàn chủ động và tiện nghi có thể thay đổi theo năm/phiên bản, vì vậy danh sách dưới đây chỉ mang tính định hướng và cần đối chiếu với xe thực tế trước khi đặt cọc.',
    details_json = JSON_SET(
        CASE WHEN JSON_VALID(details_json) THEN details_json ELSE '{}' END,
        '$.safety', JSON_ARRAY('Hệ thống chống bó cứng phanh và cân bằng điện tử (đối chiếu theo xe)', 'Hỗ trợ khởi hành ngang dốc (tùy phiên bản)', 'Camera lùi và cảm biến hỗ trợ đỗ xe (kiểm tra theo cấu hình)', 'Các tính năng Hyundai SmartSense thay đổi theo phiên bản'),
        '$.comfort', JSON_ARRAY('Khoang cabin 5 chỗ', 'Điều hòa và cửa gió cho hành khách (tùy phiên bản)', 'Màn hình giải trí và kết nối (đối chiếu theo xe)', 'Hàng ghế sau gập để tăng không gian hành lý'),
        '$.highlights', JSON_ARRAY('Động cơ xăng 2.0L theo cấu hình tham khảo', 'Hộp số tự động 6 cấp', 'Kiểu thân xe SUV 5 chỗ', 'Khoang cabin phù hợp sử dụng gia đình', 'Cần kiểm tra trang bị an toàn theo phiên bản đang bán'),
        '$.pros', JSON_ARRAY('Kích thước phù hợp cho gia đình nhỏ và nhu cầu đi lại thường ngày', 'Tư thế ngồi cao hỗ trợ quan sát giao thông', 'Hộp số tự động thuận tiện khi đi trong phố', 'Cấu hình 5 chỗ giúp sử dụng khoang hành lý linh hoạt', 'Có thể so sánh các phiên bản trước khi lựa chọn gói trang bị'),
        '$.cons', JSON_ARRAY('Không gian và tiện nghi cần được so sánh với xe cùng phân khúc', 'Mức tiêu hao thực tế thay đổi đáng kể theo giao thông và tải trọng', 'Trang bị hỗ trợ lái có thể không giống nhau giữa các phiên bản', 'Cần xác nhận năm sản xuất, động cơ và giá ưu đãi tại thời điểm mua', 'Chi phí lăn bánh và phụ kiện phát sinh chưa nằm trong giá niêm yết')
    )
WHERE slug = 'hyundai-tucson-2.0';
