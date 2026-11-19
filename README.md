# Tu Tru Auto

Website giới thiệu và bán xe ô tô bằng PHP 8.1+, PDO, MySQL/MariaDB và Apache.

## Chạy trên XAMPP

1. Bật Apache và MySQL trong XAMPP.
2. Mở phpMyAdmin, nhập `database/schema.sql`, sau đó nhập `database/seed.sql`.
3. Sao chép `.env.example` thành `.env` và cập nhật thông tin MySQL nếu khác cấu hình mặc định.
4. Mở `http://localhost/Tu-Tru-Auto/`.

Trang chủ, danh sách xe, lọc/tìm kiếm, banner và trang chi tiết dùng dữ liệu trong bảng `cars`. Ảnh mẫu được tải từ Unsplash nên cần kết nối internet để hiển thị. Sau khi cài mới, chạy tiếp `database/migrations/005_admin_contacts_payment_catalog.sql` để nạp thêm xe mẫu, thư viện ảnh, thông số và danh mục.

### Cập nhật cơ sở dữ liệu hiện có

Nếu website đã được cài đặt trước đó, chọn cơ sở dữ liệu `tu_tru_auto` trong phpMyAdmin và chạy lần lượt các migration `002_car_details_and_sales.sql`, `003_customer_orders.sql`, `004_order_delivery_details.sql` và `005_admin_contacts_payment_catalog.sql`. Migration 005 bổ sung quản lý danh mục, lưu thông tin thanh toán, hộp thư liên hệ và một số xe mẫu. Với cài đặt mới, `database/schema.sql` đã có cấu trúc mới; nhập `database/seed.sql` rồi chạy migration 005.

Khách hàng cần đăng nhập mới được đặt cọc; tài khoản quản trị không thể đặt cọc. Tiền cọc được tính tự động bằng 10% giá sau ưu đãi. Khách xem trạng thái, chi tiết, hướng dẫn chuyển khoản và lịch nhận xe tại **Tài khoản → Đơn đặt cọc của tôi**. Quản trị viên tìm kiếm đơn tại `/admin/don-hang`, xác nhận trạng thái và hẹn ngày giờ nhận xe. Cấu hình số tài khoản nhận tiền tại **Quản trị → Thanh toán**; giao dịch được nhân viên xác nhận thủ công, website chưa tích hợp cổng thanh toán.

Quản trị viên có thanh điều hướng bên trái để quản lý xe, thêm thương hiệu/kiểu dáng, tài khoản, liên hệ của khách hàng, đơn đặt cọc và thông tin thanh toán. Form liên hệ tại trang chủ và yêu cầu tư vấn/lái thử/báo giá ở trang chi tiết xe đều xuất hiện trong **Quản trị → Liên hệ khách hàng**. Trang so sánh hỗ trợ hai hoặc ba xe; công cụ trả góp chỉ đưa ra ước tính theo lãi suất do khách nhập và không thay thế báo giá ngân hàng.

### Dữ liệu chi tiết xe trong trang quản trị

Trong **Quản trị → Xe → Sửa xe**, có thể nhập danh sách ảnh (mỗi URL HTTPS hoặc đường dẫn `uploads/cars/...` trên một dòng), URL video HTTPS và JSON cho thông số, biến thể, màu sắc, tùy chọn, trang bị và điểm nổi bật. Ví dụ:

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

Xe đã qua sử dụng có thể bổ sung `mileage`, `owners`, `license_plate` và `inspection` trong nhóm `specs`. Voucher mẫu `TUTRU5` giảm 5% cho đơn từ 500 triệu đồng; voucher được kiểm tra lại phía máy chủ khi gửi yêu cầu.

## Tài khoản quản trị

1. Đăng ký tài khoản trên website tại `/dang-ky`.
2. Trong phpMyAdmin, chọn cơ sở dữ liệu `tu_tru_auto`, mở tab **SQL**, rồi cấp quyền quản trị cho email vừa đăng ký bằng câu lệnh sau (thay email mẫu bằng email thật):

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
   ```

3. Đăng xuất rồi đăng nhập lại tại `/dang-nhap`. Tài khoản admin được chuyển đến trang `/admin`.

Đăng ký công khai luôn tạo tài khoản `customer`; không có biểu mẫu công khai để tự cấp quyền admin. Trang `/admin` kiểm tra quyền ở phía máy chủ.
