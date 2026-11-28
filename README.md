# Tu Tru Auto

Website giới thiệu và bán xe ô tô bằng PHP 8.1+, PDO, MySQL/MariaDB và Apache.

## Chạy trên XAMPP

1. Bật Apache và MySQL trong XAMPP.
2. Mở phpMyAdmin, nhập `database/schema.sql`, sau đó nhập `database/seed.sql`.
3. Chạy các migration cần thiết trong `database/migrations/` trên cơ sở dữ liệu `tu_tru_auto`, gồm `002_car_details_and_sales.sql`, `003_customer_orders.sql`, `004_order_delivery_details.sql`, `005_admin_contacts_payment_catalog.sql`, `006_new_cars_and_gallery.sql`, `007_car_inventory.sql`, `008_expand_new_car_details.sql`, `009_add_apollo_and_lamborghini.sql`, `009_voucher_management.sql`, `010_model_specific_car_images.sql` và `011_support_chat.sql`.
4. Sao chép `.env.example` thành `.env` và cập nhật thông tin MySQL nếu khác cấu hình mặc định.
5. Mở `http://localhost/Tu-Tru-Auto/`.


Trang chủ, danh sách xe, lọc/tìm kiếm, banner và trang chi tiết dùng dữ liệu trong bảng `cars`. Ảnh mẫu hiện dùng URL ngoài nên cần kết nối internet để hiển thị. Sau khi cài mới, chạy tiếp các migration `005_admin_contacts_payment_catalog.sql`, `006_new_cars_and_gallery.sql`, `007_car_inventory.sql`, `008_expand_new_car_details.sql`, `009_voucher_management.sql` và `010_order_refund_and_history.sql` để nạp xe mẫu, thông số, danh mục, tồn kho, nội dung chi tiết, cấu hình voucher và quản lý hủy/hoàn cọc.

### Cập nhật cơ sở dữ liệu hiện có

Nếu website đã được cài đặt trước đó, chọn cơ sở dữ liệu `tu_tru_auto` trong phpMyAdmin và chạy lần lượt các migration `002_car_details_and_sales.sql`, `003_customer_orders.sql`, `004_order_delivery_details.sql`, `005_admin_contacts_payment_catalog.sql`, `006_new_cars_and_gallery.sql`, `007_car_inventory.sql`, `008_expand_new_car_details.sql`, `009_voucher_management.sql` và `010_order_refund_and_history.sql`. Migration 007 thêm số xe hiện có tại cửa hàng (để trống nếu chưa kiểm kê) và thời gian dự kiến nhận xe khi hết hàng. Migration 008 mở rộng nội dung chi tiết và ghi rõ các trang bị cần xác nhận theo phiên bản thực tế. Migration 009 bổ sung tên voucher, trần giảm giá, giới hạn lượt dùng, phạm vi áp dụng và bảng lịch sử `voucher_usages`. Migration 010 thêm thông tin đối soát/hủy/hoàn cọc, trạng thái `refunded` và bảng lịch sử `customer_order_status_history`.
=======
Trang chủ, danh sách xe, lọc/tìm kiếm, banner và trang chi tiết dùng dữ liệu trong bảng `cars`. Ảnh mẫu hiện dùng URL ngoài nên cần kết nối internet để hiển thị. Các migration cập nhật dữ liệu xe mẫu, thông số, danh mục, tồn kho, nội dung chi tiết, cấu hình voucher, ảnh xe và các bảng chat hỗ trợ.

Danh sách **Mua xe** hiển thị tối đa 9 xe mỗi trang. Bộ lọc và thứ tự sắp xếp được giữ lại khi chuyển trang.

### Cập nhật cơ sở dữ liệu hiện có

Nếu website đã được cài đặt trước đó, chọn cơ sở dữ liệu `tu_tru_auto` trong phpMyAdmin và chạy lần lượt các migration `002_car_details_and_sales.sql`, `003_customer_orders.sql`, `004_order_delivery_details.sql`, `005_admin_contacts_payment_catalog.sql`, `006_new_cars_and_gallery.sql`, `007_car_inventory.sql`, `008_expand_new_car_details.sql`, `009_add_apollo_and_lamborghini.sql`, `009_voucher_management.sql`, `010_model_specific_car_images.sql` và `011_support_chat.sql`. Migration 007 thêm số xe hiện có tại cửa hàng (để trống nếu chưa kiểm kê) và thời gian dự kiến nhận xe khi hết hàng. Migration 008 mở rộng nội dung chi tiết và ghi rõ các trang bị cần xác nhận theo phiên bản thực tế. Migration 009 bổ sung tên voucher, trần giảm giá, giới hạn lượt dùng, phạm vi áp dụng và bảng lịch sử `voucher_usages`. Migration 011 tạo các bảng lưu cuộc trò chuyện hỗ trợ và tin nhắn; với cơ sở dữ liệu mới, các bảng này đã có trong `schema.sql`.


Khách hàng cần đăng nhập mới được đặt cọc; tài khoản quản trị không thể đặt cọc. Tiền cọc được tính tự động bằng 10% giá sau ưu đãi. Khách xem trạng thái, chi tiết, hướng dẫn chuyển khoản và lịch nhận xe tại **Tài khoản → Đơn đặt cọc của tôi**. Quản trị viên tìm kiếm đơn tại `/admin/don-hang`, cập nhật trạng thái, hẹn ngày giờ nhận xe, ghi tham chiếu/ghi chú đối soát, hủy đơn và ghi nhận hoàn cọc (chuyển khoản hoặc tiền mặt). Mọi thay đổi được lưu trong `customer_order_status_history` và hiển thị dạng lịch sử cho cả admin lẫn khách hàng; chuyển trạng thái được kiểm tra hợp lệ và thực hiện trong transaction. Cấu hình số tài khoản nhận tiền tại **Quản trị → Thanh toán**; giao dịch được nhân viên xác nhận thủ công, website chưa tích hợp cổng thanh toán.

Quản trị viên có thanh điều hướng bên trái để quản lý xe, thêm thương hiệu/kiểu dáng, tài khoản, liên hệ của khách hàng, đơn đặt cọc, mã giảm giá và thông tin thanh toán.
 Form liên hệ tại trang chủ và yêu cầu tư vấn/lái thử/báo giá ở trang chi tiết xe đều xuất hiện trong **Quản trị → Liên hệ khách hàng**. Trang so sánh hỗ trợ hai hoặc ba xe; công cụ trả góp chỉ đưa ra ước tính theo lãi suất do khách nhập và không thay thế báo giá ngân hàng.

### Chat hỗ trợ

Khách hàng đã đăng nhập sẽ thấy bong bóng chat cố định ở góc trang. Có thể kéo bong bóng hoặc khung chat để di chuyển, thu nhỏ khung chat về bong bóng, và xem số tin nhắn mới chưa đọc trên huy hiệu. Nhấn **Enter** để gửi tin nhắn; dùng **Shift+Enter** để xuống dòng. Số điện thoại hiển thị trong danh sách chat quản trị được lấy từ hồ sơ khách hàng; nếu chưa có sẽ hiện thông báo tương ứng.

Quản trị viên mở **Quản trị → Chat hỗ trợ** (`/admin/chat`) để xem danh sách hội thoại, số tin nhắn chưa đọc và trả lời khách. Danh sách và tin nhắn được làm mới định kỳ.

### Dữ liệu chi tiết xe trong trang quản trị

Trong **Quản trị → Xe → Sửa xe**, có thể nhập danh sách ảnh (mỗi URL HTTPS hoặc đường dẫn `uploads/cars/...` trên một dòng), URL video HTTPS, số lượng tại cửa hàng, thời gian chờ dự kiến và JSON cho thông số, biến thể, màu sắc, tùy chọn, trang bị và điểm nổi bật. Dùng ảnh đã xác minh đúng hãng và đúng mẫu xe; thư viện ảnh mẫu hiện tại chưa đủ 10 ảnh xác thực cho từng xe. Ví dụ:

```json
{
  "specs": {
    "model_line": "Tên dòng xe",
    "engine": "Thông tin động cơ theo phiên bản",
    "displacement": "Dung tích theo thông số nhà sản xuất",
    "power": "Công suất theo thông số nhà sản xuất",
    "torque": "Mô-men xoắn theo thông số nhà sản xuất",
    "consumption": "Mức tiêu hao theo công bố",
    "dimensions": "Dài × rộng × cao theo thông số nhà sản xuất",
    "wheelbase": "Chiều dài cơ sở",
    "ground_clearance": "Khoảng sáng gầm",
    "seats": "Số chỗ",
    "doors": "Số cửa",
    "tank": "Dung tích bình xăng/pin",
    "trunk": "Dung tích cốp",
    "origin": "Xuất xứ"
  },
  "variants": [
    {"name": "Base", "price": 0},
    {"name": "Premium", "price": 150000000},
    {"name": "Luxury", "price": 300000000}
  ],
  "colors": {
    "exterior": ["Trắng", "Đen", "Bạc"],
    "interior": ["Đen", "Be"]
  },
  "options": [
    {"name": "Gói hỗ trợ lái", "price": 25000000}
  ],
  "safety": ["Phanh ABS", "Cảnh báo điểm mù"],
  "comfort": ["Điều hòa tự động", "Cửa sổ trời"],
  "highlights": ["Thiết kế hiện đại"],
  "pros": ["Không gian rộng"],
  "cons": ["Chi phí bảo dưỡng cần cân nhắc"]
}
```

Xe đã qua sử dụng có thể bổ sung `mileage`, `owners`, `license_plate` và `inspection` trong nhóm `specs`. Voucher mẫu `TUTRU5` giảm 5% (tối đa 100 triệu đồng) cho đơn từ 500 triệu đồng. Quản trị viên cấu hình mã tại **Quản trị → Mã giảm giá**: hỗ trợ giảm theo phần trăm hoặc số tiền cố định, trần giảm giá, giá trị đơn tối thiểu, giới hạn tổng lượt dùng/lượt mỗi khách, phạm vi tất cả xe/hãng xe/mẫu xe và gán riêng cho một tài khoản. Mã được kiểm tra lại phía máy chủ khi xem trước và khi tạo đơn; lượt dùng được khóa trong transaction để tránh áp dụng vượt giới hạn.

## Tài khoản quản trị

1. Đăng ký tài khoản trên website tại `/dang-ky`.
2. Trong phpMyAdmin, chọn cơ sở dữ liệu `tu_tru_auto`, mở tab **SQL**, rồi cấp quyền quản trị cho email vừa đăng ký bằng câu lệnh sau (thay email mẫu bằng email thật):

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
   ```

3. Đăng xuất rồi đăng nhập lại tại `/dang-nhap`. Tài khoản admin được chuyển đến trang `/admin`.

Đăng ký công khai luôn tạo tài khoản `customer`; không có biểu mẫu công khai để tự cấp quyền admin. Trang `/admin` kiểm tra quyền ở phía máy chủ.
