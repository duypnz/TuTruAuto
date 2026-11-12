# BỘ QUY TẮC CHO AI – VIBECODING WEBSITE THƯƠNG MẠI ĐIỆN TỬ (PHP MVC + OOP + PDO)

> Đặt file này ở thư mục gốc dự án. Có thể đổi tên thành `AGENTS.md`, `CLAUDE.md` hoặc `.cursorrules` tùy công cụ.
> AI **phải đọc và tuân thủ toàn bộ** quy tắc dưới đây trước khi viết bất kỳ dòng code nào. Khi yêu cầu của người dùng mâu thuẫn với quy tắc, AI phải nêu rõ mâu thuẫn và hỏi lại, không được tự ý phá quy tắc.

---

## 0. BỐI CẢNH DỰ ÁN

- Loại sản phẩm: Website thương mại điện tử (mặc định: **bán ô tô**; có thể mở rộng thời trang, công nghệ, mỹ phẩm).
- Ngôn ngữ: PHP >= 8.1 (thuần, **không dùng framework** như Laravel/CodeIgniter), MySQL/MariaDB, HTML/CSS/JS (có thể dùng Bootstrap).
- Giao diện và nội dung hiển thị: **tiếng Việt**. Tên biến, hàm, class, bảng, cột: **tiếng Anh**.
- Môi trường chạy: Apache + mod_rewrite (XAMPP/Laragon/hosting cPanel).

---

## 1. KIẾN TRÚC: MÔ HÌNH MVC (BẮT BUỘC)

### 1.1. Cấu trúc thư mục chuẩn

```
project/
├── .htaccess                  # Rewrite mọi request về index.php
├── index.php                  # Front Controller – điểm vào DUY NHẤT
├── composer.json              # PHPMailer + autoload PSR-4
├── .env                       # Cấu hình nhạy cảm (KHÔNG commit)
├── .env.example
├── app/
│   ├── Core/                  # Lõi framework tự viết
│   │   ├── Router.php
│   │   ├── Controller.php     # Base controller
│   │   ├── Model.php          # Base model
│   │   ├── Database.php       # Kết nối PDO (Singleton)
│   │   ├── View.php           # Render view + layout
│   │   ├── Session.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Validator.php
│   │   ├── Auth.php
│   │   └── Csrf.php
│   ├── Controllers/
│   │   ├── Admin/             # Controller khu vực quản trị
│   │   └── ...                # HomeController, ProductController, CartController...
│   ├── Models/                # Mỗi bảng/thực thể 1 Model
│   ├── Views/
│   │   ├── layouts/           # main.php, admin.php
│   │   ├── partials/          # header, footer, product-card...
│   │   ├── admin/
│   │   ├── emails/            # Template email hóa đơn
│   │   └── ...
│   ├── Services/              # Nghiệp vụ phức tạp: CartService, OrderService, VoucherService, MailService, RecommendationService
│   ├── Config/                # app.php, database.php, mail.php, routes.php
│   └── Helpers/               # Hàm tiện ích tối thiểu (format tiền, slug...)
├── public/                    # Tài nguyên tĩnh
│   ├── assets/css|js|img
│   └── uploads/               # Ảnh upload (chặn thực thi PHP)
├── database/
│   ├── schema.sql
│   └── seed.sql
├── storage/
│   ├── logs/
│   └── cache/
└── vendor/
```

### 1.2. Phân tách trách nhiệm (nghiêm ngặt)

| Tầng | Được làm | **Cấm** |
|---|---|---|
| **Model** | Truy vấn DB (PDO), ánh xạ dữ liệu, quy tắc dữ liệu | Echo/HTML, đọc `$_POST/$_GET/$_SESSION` trực tiếp, redirect |
| **View** | Hiển thị HTML, vòng lặp/điều kiện đơn giản, escape output | Truy vấn DB, gọi Model, xử lý nghiệp vụ |
| **Controller** | Nhận Request, validate, gọi Model/Service, chọn View, redirect | Viết SQL, viết HTML dài, chứa nghiệp vụ phức tạp |
| **Service** | Nghiệp vụ liên nhiều Model (đặt hàng, áp voucher, gửi mail) | Echo/HTML, đọc superglobal |

### 1.3. Định tuyến tập trung

- **Mọi request** đi qua `index.php`. Không tạo file PHP truy cập trực tiếp (ví dụ `cart.php`, `login.php`) ở ngoài.
- `.htaccess` mẫu:

```apache
RewriteEngine On
RewriteBase /

# Chặn truy cập trực tiếp thư mục nhạy cảm
RewriteRule ^(app|database|storage|vendor)/ - [F,L]

# Cho phép file/thư mục tĩnh thật
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Chặn đọc file ẩn và file cấu hình
<FilesMatch "^\.(env|git)|composer\.(json|lock)$">
    Require all denied
</FilesMatch>
```

- Khai báo route tập trung tại `app/Config/routes.php`, ví dụ:

```php
$router->get('/', [HomeController::class, 'index']);
$router->get('/san-pham/{slug}', [ProductController::class, 'show']);
$router->post('/gio-hang/them', [CartController::class, 'add']);
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($r) {
    $r->get('/san-pham', [Admin\ProductController::class, 'index']);
});
```

- URL thân thiện (slug), không dùng `?controller=...&action=...`.
- Route thay đổi dữ liệu **chỉ dùng POST** (có CSRF token), không dùng GET.

---

## 2. LẬP TRÌNH HƯỚNG ĐỐI TƯỢNG (OOP) – 100% BACKEND LÀ CLASS

### 2.1. Quy tắc chung

- **Mọi code backend nằm trong class.** Cấm viết hàm rời, cấm logic nghiệp vụ ở file không phải class. File duy nhất được phép chứa code thủ tục: `index.php` (khởi động) và `app/Config/*.php` (trả về mảng).
- Mỗi file một class; tên file = tên class (PascalCase). Dùng **namespace PSR-4** (`App\Models`, `App\Controllers`, `App\Core`, `App\Services`) và autoload bằng Composer.
- Khai báo `declare(strict_types=1);` ở đầu mỗi file PHP. Luôn khai báo kiểu tham số và kiểu trả về.

### 2.2. Đóng gói (Encapsulation)

- Thuộc tính **luôn `private` hoặc `protected`**, không bao giờ `public`. Truy cập qua getter/setter có kiểm tra dữ liệu.
- Chỉ mở `public` cho phương thức thuộc "hợp đồng" của class. Phương thức nội bộ để `private`/`protected`.
- Dùng `readonly` cho thuộc tính bất biến (ví dụ `id`, `createdAt`).
- Không để lộ đối tượng `PDO` ra ngoài tầng Core/Model.

### 2.3. Kế thừa (Inheritance) và đa hình

- `App\Core\Controller` (abstract) → mọi controller kế thừa; chứa `render()`, `redirect()`, `json()`, `requireAuth()`.
- `App\Core\Model` (abstract) → mọi model kế thừa; cung cấp CRUD chung: `find()`, `all()`, `create()`, `update()`, `delete()`, `paginate()`, `where()`.
- Admin controller kế thừa `AdminBaseController` (đã gắn kiểm tra quyền admin).
- Dùng `abstract`, `interface` khi cần (ví dụ `PaymentGatewayInterface` → `CodPayment`, `BankTransferPayment`, `VnpayPayment`).
- Ưu tiên **Dependency Injection qua constructor** cho Service; hạn chế `static` (ngoại lệ: Singleton `Database`).

### 2.4. Mẫu Base Model (AI phải bám theo)

```php
<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
    // all(), create(), update(), delete(), paginate()...
}
```

> Tên bảng/cột trong chuỗi SQL chỉ lấy từ thuộc tính cố định của class hoặc **whitelist**, tuyệt đối không nối từ input người dùng.

---

## 3. CƠ SỞ DỮ LIỆU: CHỈ DÙNG PDO + PREPARED STATEMENTS

### 3.1. Điều CẤM

- **Cấm tuyệt đối** `mysqli_*`, `mysql_*`, `new mysqli()`.
- **Cấm** nối chuỗi biến vào SQL: `"... WHERE id = $id"`, `"... '" . $_POST['x'] . "'"`.
- **Cấm** `PDO::query()` / `exec()` với dữ liệu có biến từ người dùng.
- **Cấm** tắt `ATTR_EMULATE_PREPARES => false`.

### 3.2. Điều BẮT BUỘC

- Kết nối qua class `Database` (Singleton), thông tin lấy từ `.env`:

```php
$this->pdo = new PDO(
    "mysql:host={$host};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);
```

- Mọi truy vấn có tham số dùng **placeholder có tên** (`:name`) và `execute([...])` hoặc `bindValue()` kèm kiểu (`PDO::PARAM_INT`).
- `LIMIT/OFFSET` bind dạng `PDO::PARAM_INT`.
- Mệnh đề `IN (...)`: sinh placeholder động theo số phần tử, rồi bind từng giá trị.
- `ORDER BY` động: chỉ cho phép giá trị trong **whitelist**.
- Thao tác nhiều bước (đặt hàng, trừ kho, dùng voucher): bọc trong **transaction** (`beginTransaction` / `commit` / `rollBack`).
- Bắt `PDOException`, ghi log vào `storage/logs`, **không in lỗi SQL ra người dùng**.

---

## 4. BẢO MẬT (BẮT BUỘC, KHÔNG THƯƠNG LƯỢNG)

- **XSS**: mọi dữ liệu xuất ra View phải escape bằng `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` (qua helper `e()`).
- **CSRF**: mọi form POST có token (class `Csrf`), kiểm tra ở middleware.
- **Mật khẩu**: chỉ dùng `password_hash(..., PASSWORD_DEFAULT)` và `password_verify()`. Cấm MD5/SHA1.
- **Session**: `session_regenerate_id(true)` sau đăng nhập; cookie `HttpOnly`, `SameSite=Lax`, `Secure` khi có HTTPS.
- **Phân quyền**: kiểm tra ở **Controller/Middleware phía server**, không chỉ ẩn nút ở giao diện. Chống IDOR (khách chỉ xem được đơn hàng của chính mình).
- **Validate** mọi input (kiểu, độ dài, định dạng) bằng class `Validator`. Dữ liệu giá, số lượng, tổng tiền **luôn tính lại ở server**, không tin giá từ form/session phía client.
- **Upload ảnh**: kiểm tra MIME thật (`finfo`), đuôi file whitelist (jpg/png/webp), giới hạn dung lượng, đổi tên ngẫu nhiên, chặn chạy PHP trong `public/uploads`.
- Không commit `.env`, không hard-code mật khẩu/API key/SMTP trong code.
- Giới hạn thử đăng nhập sai (rate limit đơn giản theo IP/tài khoản).

---

## 5. NGHIỆP VỤ ECOMMERCE

### 5.1. Danh mục – Sản phẩm – Biến thể

- **Danh mục** hỗ trợ cha–con (`parent_id`), có `slug`, trạng thái hiển thị.
- **Sản phẩm**: tên, slug, mô tả, ảnh đại diện + thư viện ảnh, giá gốc, giá khuyến mãi, thương hiệu, trạng thái, SEO (meta title/description).
- **Biến thể (variants)**: bảng `product_variants` riêng, mỗi biến thể có `sku`, `price`, `stock`, và các thuộc tính (size/màu/phiên bản...).
  - Thiết kế linh hoạt: `attributes` + `attribute_values` + `variant_attribute_values`, hoặc cột JSON.
  - **Thời trang**: size, màu. **Công nghệ**: dung lượng, màu. **Mỹ phẩm**: dung tích, tông màu.
  - **Ô tô**: phiên bản (Base/Premium/Luxury), màu ngoại thất, màu nội thất, động cơ/hộp số; giá theo từng phiên bản.
- Tồn kho theo **biến thể**, không theo sản phẩm cha.
- Admin CRUD đầy đủ, có phân trang, tìm kiếm, lọc, bật/tắt hiển thị, xóa mềm (`deleted_at`).

### 5.2. Giỏ hàng (Session)

- Class `CartService` quản lý giỏ trong `$_SESSION['cart']`; View/Controller không đụng trực tiếp vào mảng session.
- Khóa mỗi dòng theo `variant_id`. Chức năng: thêm, cập nhật số lượng, xóa, xóa hết, đếm, tính tạm tính.
- Mỗi lần hiển thị/thanh toán: **nạp lại giá và tồn kho từ DB**, loại bỏ sản phẩm đã ẩn/hết hàng, báo người dùng.
- Khi khách đăng nhập: gộp giỏ session với giỏ đã lưu (nếu có bảng `carts`).

### 5.3. Đặt hàng và Thanh toán

- Luồng: Giỏ hàng → Nhập thông tin (họ tên, SĐT, email, địa chỉ) → Áp voucher → Chọn phương thức thanh toán → Xác nhận → Trang cảm ơn.
- `OrderService::placeOrder()` chạy trong **một transaction**: tạo `orders` + `order_items` (lưu **snapshot** tên, giá, biến thể tại thời điểm mua) → trừ kho → ghi nhận dùng voucher → commit.
- Trạng thái đơn: `pending → confirmed → processing → shipped → completed`, hoặc `cancelled / refunded`. Chỉ cho phép chuyển trạng thái hợp lệ; hoàn kho khi hủy.
- Mã đơn duy nhất, dễ đọc (ví dụ `DH20261105-0001`).
- Thanh toán qua `PaymentGatewayInterface`; tối thiểu: **COD** và **chuyển khoản ngân hàng** (kèm nội dung chuyển khoản). Cổng VNPay/MoMo là phần mở rộng. Ghi bảng `payments` (số tiền, phương thức, trạng thái, mã giao dịch).
- **Ô tô**: ngoài thanh toán đủ, hỗ trợ **đặt cọc** (tỉ lệ % hoặc số tiền cố định), trạng thái `deposit_paid`; đơn không giao ngay mà chuyển sang quy trình xử lý hồ sơ/giao xe.

### 5.4. Tài khoản và Phân quyền

- Vai trò: `admin`, `customer` (có thể mở rộng `staff`). Bảng `users` có cột `role`, `status`.
- Chức năng khách: đăng ký, đăng nhập, quên/đổi mật khẩu, hồ sơ, sổ địa chỉ, lịch sử đơn, xem chi tiết/hủy đơn khi còn hợp lệ.
- Chức năng admin: dashboard thống kê, quản lý danh mục/sản phẩm/biến thể/đơn hàng/người dùng/voucher.
- Dùng `Auth` + middleware `AuthMiddleware`, `AdminMiddleware`. Khu vực `/admin` chỉ admin truy cập.

---

## 6. TÍNH NĂNG NÂNG CAO

### 6.1. Gợi ý sản phẩm liên quan thông minh (`RecommendationService`)

Chấm điểm và sắp xếp theo thứ tự ưu tiên, **chỉ dùng SQL/PHP thuần**, không phụ thuộc dịch vụ ngoài:

1. **Cùng danh mục** (trọng số cao) và/hoặc **cùng thương hiệu**.
2. **Khoảng giá tương đồng** (±20–30%).
3. **Mua cùng nhau**: sản phẩm xuất hiện chung trong `order_items` với sản phẩm hiện tại.
4. **Thuộc tính tương đồng** (phân khúc, kiểu dáng, nhiên liệu…).
5. Bổ sung theo **lượt xem/bán chạy** khi chưa đủ số lượng.

Quy tắc: loại chính sản phẩm đang xem và sản phẩm hết hàng/ẩn; giới hạn 4–8 sản phẩm; có thể cache kết quả vào `storage/cache` hoặc bảng riêng; hiển thị ở trang chi tiết, giỏ hàng ("Có thể bạn cũng thích") và trang cảm ơn.

### 6.2. Gửi email hóa đơn tự động (PHPMailer)

- Cài bằng Composer (`phpmailer/phpmailer`). Cấm dùng `mail()` thuần.
- Đóng gói trong `MailService` (cấu hình SMTP từ `.env`; không hard-code mật khẩu).
- Gửi email **hóa đơn** khi đặt hàng thành công và email cập nhật khi đổi trạng thái; template HTML ở `app/Views/emails/` (có nội dung text thay thế).
- Nội dung hóa đơn: mã đơn, ngày, thông tin người nhận, bảng sản phẩm (biến thể, SL, đơn giá, thành tiền), giảm giá/voucher, phí vận chuyển, tổng cộng, phương thức thanh toán.
- Gửi mail **không được làm hỏng đơn hàng**: lỗi gửi mail chỉ ghi log và đưa vào hàng đợi gửi lại (`email_queue`), đơn vẫn được tạo.
- Có thể đính kèm hóa đơn PDF (tùy chọn).

### 6.3. Mã giảm giá và voucher khuyến mãi (`VoucherService`)

Bảng `vouchers`: `code` (duy nhất), `type`, `value`, `max_discount`, `min_order_value`, `usage_limit`, `usage_per_user`, `used_count`, `start_at`, `end_at`, `status`, phạm vi áp dụng (`all` / danh mục / sản phẩm); bảng `voucher_usages` ghi lịch sử dùng.

Các loại voucher:

| Loại | Mô tả |
|---|---|
| `percent` | Giảm theo %, có trần `max_discount` |
| `fixed` | Giảm số tiền cố định |
| `free_shipping` | Miễn/giảm phí vận chuyển |
| `gift` | Tặng quà/phụ kiện kèm theo |

**Bộ voucher đặc thù cho web bán ô tô** (AI nên seed mẫu và hỗ trợ trong admin):

- **Ưu đãi đặt cọc**: giảm giá hoặc tặng quà khi đặt cọc online trước hạn.
- **Tặng gói phụ kiện/bảo dưỡng**: phim cách nhiệt, camera hành trình, thảm sàn, bảo dưỡng miễn phí lần đầu (loại `gift`).
- **Hỗ trợ lệ phí**: trừ trực tiếp một phần phí trước bạ/đăng ký (loại `fixed`).
- **Khách hàng cũ/giới thiệu**: mã riêng theo người dùng (`user_id` hoặc `referral_code`).
- **Đổi xe cũ lấy xe mới** (trade-in): cộng thêm ưu đãi theo điều kiện.
- **Flash sale/sự kiện theo thời gian** (cuối năm, lễ lớn) và **theo phiên bản/dòng xe** cụ thể.
- **Voucher theo giá trị**: giá trị xe lớn nên luôn đặt `max_discount`, `min_order_value` hợp lý để tránh lỗ.

Quy tắc kiểm tra (chạy ở server, trong transaction): mã tồn tại → đang bật → trong thời gian hiệu lực → đạt giá trị tối thiểu → còn lượt dùng (toàn hệ thống và theo người dùng) → đúng phạm vi áp dụng → chống dùng trùng/đua lệnh (khóa dòng `SELECT ... FOR UPDATE`). Chính sách cộng dồn voucher phải rõ ràng (mặc định: **1 voucher/đơn**). Trả về thông báo lỗi tiếng Việt cụ thể cho từng trường hợp.

---

## 7. QUY ƯỚC MÃ NGUỒN

- Chuẩn **PSR-12**; thụt lề 4 dấu cách; tên class `PascalCase`, phương thức/biến `camelCase`, bảng/cột DB `snake_case` (bảng số nhiều: `products`, `orders`).
- Controller mỏng, Service dày; mỗi phương thức làm **một việc**, ngắn gọn (khoảng ≤ 40 dòng).
- Controller đặt tên `XxxController`, Model đặt tên số ít (`Product`, `Order`), Service `XxxService`.
- Comment/PHPDoc bằng tiếng Việt cho phần nghiệp vụ phức tạp; không comment hiển nhiên.
- Xử lý lỗi bằng **exception** có lớp riêng (`ValidationException`, `NotFoundException`, `AuthorizationException`); có trang 404/403/500 tử tế.
- Định dạng tiền VND bằng helper (`1.250.000 ₫`); lưu DB dạng `DECIMAL(15,0)`, **không lưu kiểu float**.
- Charset toàn hệ thống `utf8mb4`; múi giờ `Asia/Ho_Chi_Minh`.
- Có xác thực + phân trang + tìm kiếm cho mọi danh sách admin.
- Giao diện responsive (mobile-first), ưu tiên tốc độ: tối ưu ảnh, lazy-load, index DB cho cột tìm kiếm/khóa ngoại.

---

## 8. QUY TRÌNH LÀM VIỆC CỦA AI (VIBECODING)

1. **Đọc trước, viết sau**: xem cấu trúc hiện có, tái sử dụng Base Class/Service, không tạo trùng chức năng.
2. **Lập kế hoạch ngắn** (liệt kê file sẽ tạo/sửa) trước khi code các tính năng lớn.
3. **Làm từng bước nhỏ**, mỗi lượt một tính năng hoàn chỉnh (Model + Controller + View + Route + SQL cần thiết). Không để code dở dang hay `TODO` mơ hồ.
4. **Xuất đầy đủ file** khi tạo mới (có namespace, `use`, `declare(strict_types=1)`); khi sửa, nêu rõ đường dẫn file và đoạn thay đổi.
5. Mọi thay đổi cấu trúc DB phải kèm **câu lệnh SQL** (cập nhật `database/schema.sql`) và dữ liệu mẫu nếu cần.
6. **Tự kiểm tra trước khi trả lời** theo checklist bên dưới.
7. Cuối mỗi lượt: tóm tắt việc đã làm, cách chạy/kiểm thử, và các bước tiếp theo đề xuất.
8. Khi yêu cầu mơ hồ: **hỏi tối đa 1–2 câu then chốt**, nếu không chặn tiến độ thì nêu giả định rồi làm tiếp.
9. Không cài thêm thư viện ngoài danh sách (PHPMailer, và tối đa vài thư viện nhỏ thật cần thiết) nếu chưa được đồng ý.

### Checklist tự kiểm tra (Definition of Done)

- [ ] Không có `mysqli_*`; mọi SQL dùng PDO + prepared statements.
- [ ] Không có SQL/HTML trong Controller; không có SQL trong View; không có HTML trong Model.
- [ ] Mọi code backend nằm trong class có namespace; thuộc tính `private/protected`.
- [ ] Route khai báo tập trung, đi qua `index.php`.
- [ ] Output đã escape; form POST có CSRF; input đã validate.
- [ ] Kiểm tra quyền ở server cho mọi hành động nhạy cảm.
- [ ] Thao tác nhiều bước nằm trong transaction.
- [ ] Giá/tổng tiền/voucher tính lại ở server.
- [ ] Không lộ thông tin nhạy cảm trong code hoặc thông báo lỗi.
- [ ] Code chạy được ngay (đủ `use`, đúng namespace, đúng đường dẫn).

---

## 9. DANH SÁCH CẤM TỔNG HỢP

- ❌ `mysqli_*`, `mysql_*`, nối chuỗi biến vào SQL.
- ❌ File PHP truy cập trực tiếp ngoài `index.php` (trừ `public/` tĩnh).
- ❌ Hàm/logic thủ tục rời rạc ngoài class (backend).
- ❌ Thuộc tính `public` chứa dữ liệu nội bộ.
- ❌ Truy vấn DB hoặc logic nghiệp vụ trong View.
- ❌ MD5/SHA1 cho mật khẩu; lưu mật khẩu/API key dạng văn bản trong code.
- ❌ Tin giá/số lượng/tổng tiền/mã giảm giá từ phía client.
- ❌ Dùng `mail()` thay PHPMailer; để lỗi gửi mail làm hỏng đơn hàng.
- ❌ In lỗi hệ thống/SQL ra màn hình người dùng.
- ❌ Dùng `float` để lưu tiền; dùng `eval`, `extract` trên input, `$_REQUEST` bừa bãi.
- ❌ Tự ý thêm framework, đổi kiến trúc, hoặc bỏ qua quy tắc trong file này.

---

## 10. LỘ TRÌNH TRIỂN KHAI GỢI Ý (CHO AI ĐI THEO THỨ TỰ)

1. Khung dự án: `.htaccess`, `index.php`, `Router`, `Controller`, `Model`, `Database`, `View`, `Session`, `Csrf`, `Validator`, autoload Composer.
2. Auth: đăng ký/đăng nhập/đăng xuất, middleware, phân quyền Admin/Khách.
3. Admin: danh mục → sản phẩm → biến thể → upload ảnh.
4. Front: trang chủ, danh sách (lọc/sắp xếp/phân trang), chi tiết sản phẩm, tìm kiếm.
5. Giỏ hàng Session → Checkout → Đặt hàng (transaction) → Thanh toán.
6. Voucher/mã giảm giá (admin + áp dụng ở checkout).
7. PHPMailer + email hóa đơn tự động.
8. Gợi ý sản phẩm liên quan.
9. Dashboard thống kê, tối ưu hiệu năng, rà soát bảo mật, hoàn thiện `schema.sql` + `seed.sql`.
