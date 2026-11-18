# Tu Tru Auto

Website giới thiệu và bán xe ô tô bằng PHP 8.1+, PDO, MySQL/MariaDB và Apache.

## Chạy trên XAMPP

1. Bật Apache và MySQL trong XAMPP.
2. Mở phpMyAdmin, nhập `database/schema.sql`, sau đó nhập `database/seed.sql`.
3. Sao chép `.env.example` thành `.env` và cập nhật thông tin MySQL nếu khác cấu hình mặc định.
4. Mở `http://localhost/Tu-Tru-Auto/`.

Trang chủ, danh sách xe, lọc/tìm kiếm và trang chi tiết dùng dữ liệu trong bảng `cars`. Ảnh mẫu được tải từ Unsplash nên cần kết nối internet để hiển thị.

### Cập nhật cơ sở dữ liệu hiện có

Nếu website đã được cài đặt trước đó, chọn cơ sở dữ liệu `tu_tru_auto` trong phpMyAdmin và chạy một lần nội dung `database/migrations/002_car_details_and_sales.sql`. Migration bổ sung dữ liệu chi tiết xe, đánh giá, voucher và yêu cầu tư vấn. Với cài đặt mới, `database/schema.sql` đã có các bảng/cột mới; sau đó nhập `database/seed.sql`.

Chạy thêm `database/migrations/003_customer_orders.sql` để bật giỏ hàng và theo dõi đơn. Khách hàng đăng nhập có thể xem lịch sử tại **Tài khoản → Đơn mua xe & đặt cọc**; quản trị viên xem tất cả đơn tại `/admin/don-hang` và cập nhật trạng thái **Chờ xác nhận**, **Đã đặt cọc**, **Đã mua xe** hoặc **Đã hủy**. Trạng thái đặt cọc/mua xe do nhân viên xác nhận thủ công, website chưa tích hợp cổng thanh toán.

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
