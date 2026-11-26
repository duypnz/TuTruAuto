# Tu Tru Auto

Website giới thiệu và bán xe ô tô bằng PHP 8.1+, PDO, MySQL/MariaDB và Apache.

## Chạy trên XAMPP

1. Bật Apache và MySQL trong XAMPP.
2. Mở phpMyAdmin, nhập `database/schema.sql`, sau đó nhập `database/seed.sql`.
3. Chạy lần lượt các tệp trong `database/migrations/002_...sql` đến `009_add_apollo_and_lamborghini.sql` trên cơ sở dữ liệu `tu_tru_auto` để cập nhật dữ liệu và thêm hai mẫu Apollo, Lamborghini.
4. Sao chép `.env.example` thành `.env` và cập nhật thông tin MySQL nếu khác cấu hình mặc định.
5. Mở `http://localhost/Tu-Tru-Auto/`.

Trang chủ, danh sách xe, lọc/tìm kiếm và trang chi tiết dùng dữ liệu trong bảng `cars`. Ảnh mẫu được tải từ Unsplash nên cần kết nối internet để hiển thị.

## Tài khoản quản trị

1. Đăng ký tài khoản trên website tại `/dang-ky`.
2. Trong phpMyAdmin, chọn cơ sở dữ liệu `tu_tru_auto`, mở tab **SQL**, rồi cấp quyền quản trị cho email vừa đăng ký bằng câu lệnh sau (thay email mẫu bằng email thật):

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
   ```

3. Đăng xuất rồi đăng nhập lại tại `/dang-nhap`. Tài khoản admin được chuyển đến trang `/admin`.

Đăng ký công khai luôn tạo tài khoản `customer`; không có biểu mẫu công khai để tự cấp quyền admin. Trang `/admin` kiểm tra quyền ở phía máy chủ.
