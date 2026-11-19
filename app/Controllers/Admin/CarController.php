<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Car;
use App\Models\CatalogOption;

final class CarController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->render('admin/cars/index', [
            'title' => 'Quản lý xe',
            'cars' => (new Car())->allForAdmin(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function createForm(): void
    {
        $this->requireAdmin();
        $this->renderCarForm([
            'title' => 'Thêm xe mới',
            'car' => null,
            'errors' => [],
            'old' => [],
            'csrf' => Csrf::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->renderCarForm([
                'title' => 'Thêm xe mới',
                'car' => null,
                'errors' => ['_form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST,
                'csrf' => Csrf::token(),
            ]);
            return;
        }

        $errors = [];
        $data = $this->validate($_POST, $_FILES, true, $errors);
        if ($errors) {
            $this->renderCarForm([
                'title' => 'Thêm xe mới',
                'car' => null,
                'errors' => $errors,
                'old' => $_POST,
                'csrf' => Csrf::token(),
            ]);
            return;
        }

        $imagePath = $this->handleUpload($_FILES['image'] ?? [], $errors);
        if ($errors) {
            $this->renderCarForm([
                'title' => 'Thêm xe mới',
                'car' => null,
                'errors' => $errors,
                'old' => $_POST,
                'csrf' => Csrf::token(),
            ]);
            return;
        }
        $data['image_url'] = $imagePath;
        $data['slug'] = (new Car())->uniqueSlug(Car::slugify($data['name']));

        (new Car())->create($data);
        $_SESSION['_flash'] = 'Đã thêm xe mới thành công.';
        $this->redirect('/admin/xe');
    }

    public function editForm(array $params): void
    {
        $this->requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $car = (new Car())->findById($id);
        if (!$car) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->renderCarForm([
            'title' => 'Sửa xe: ' . $car['name'],
            'car' => $car,
            'errors' => [],
            'old' => [],
            'csrf' => Csrf::token(),
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $car = (new Car())->findById($id);
        if (!$car) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->renderCarForm([
                'title' => 'Sửa xe: ' . $car['name'],
                'car' => $car,
                'errors' => ['_form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST,
                'csrf' => Csrf::token(),
            ]);
            return;
        }

        $errors = [];
        $data = $this->validate($_POST, $_FILES, false, $errors, $id);
        if ($errors) {
            $this->renderCarForm([
                'title' => 'Sửa xe: ' . $car['name'],
                'car' => $car,
                'errors' => $errors,
                'old' => $_POST,
                'csrf' => Csrf::token(),
            ]);
            return;
        }

        if ($data['car_code'] === '') {
            $data['car_code'] = (string) ($car['car_code'] ?? '');
        }
        $imagePath = $car['image_url'];
        if (!empty($_FILES['image']['tmp_name'])) {
            $newPath = $this->handleUpload($_FILES['image'], $errors);
            if ($errors) {
                $this->renderCarForm([
                    'title' => 'Sửa xe: ' . $car['name'],
                    'car' => $car,
                    'errors' => $errors,
                    'old' => $_POST,
                    'csrf' => Csrf::token(),
                ]);
                return;
            }
            $imagePath = $newPath;
            $this->deleteImageFile($car['image_url']);
        }
        $data['image_url'] = $imagePath;

        $baseSlug = Car::slugify($data['name']);
        if ($baseSlug !== Car::slugify($car['name'])) {
            $data['slug'] = (new Car())->uniqueSlug($baseSlug, $id);
        } else {
            $data['slug'] = $car['slug'];
        }

        (new Car())->update($id, $data);
        $_SESSION['_flash'] = 'Đã cập nhật xe thành công.';
        $this->redirect('/admin/xe');
    }

    public function delete(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn.';
            return;
        }
        $id = (int) ($params['id'] ?? 0);
        $car = (new Car())->findById($id);
        if ($car) {
            $this->deleteImageFile($car['image_url']);
            (new Car())->delete($id);
            $_SESSION['_flash'] = 'Đã xóa xe.';
        }
        $this->redirect('/admin/xe');
    }

    private function validate(array $post, array $files, bool $requireImage, array &$errors, ?int $excludeId = null): array
    {
        $data = [];
        $name = trim((string) ($post['name'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 180) {
            $errors['name'] = 'Tên xe phải từ 2 đến 180 ký tự.';
        }
        $data['name'] = $name;

        $brand = trim((string) ($post['brand'] ?? ''));
        if (!in_array($brand, (new CatalogOption())->values('brand'), true) || mb_strlen($brand) > 80) {
            $errors['brand'] = 'Vui lòng chọn/nhập thương hiệu hợp lệ.';
        }
        $data['brand'] = $brand;

        $year = filter_var($post['year'] ?? null, FILTER_VALIDATE_INT);
        if ($year === false || $year < 1990 || $year > 2100) {
            $errors['year'] = 'Năm sản xuất không hợp lệ.';
        }
        $data['year'] = $year === false ? 0 : $year;

        $bodyTypes = (new CatalogOption())->values('body_type');
        $bodyType = trim((string) ($post['body_type'] ?? ''));
        if (!in_array($bodyType, $bodyTypes, true)) {
            $errors['body_type'] = 'Kiểu dáng không hợp lệ.';
        }
        $data['body_type'] = $bodyType;

        $fuels = ['Xăng', 'Hybrid', 'Điện', 'Dầu'];
        $fuel = trim((string) ($post['fuel'] ?? ''));
        if (!in_array($fuel, $fuels, true)) {
            $errors['fuel'] = 'Nhiên liệu không hợp lệ.';
        }
        $data['fuel'] = $fuel;

        $transmission = trim((string) ($post['transmission'] ?? ''));
        if ($transmission === '' || mb_strlen($transmission) > 50) {
            $errors['transmission'] = 'Hộp số không hợp lệ.';
        }
        $data['transmission'] = $transmission;

        $price = filter_var($post['price'] ?? null, FILTER_VALIDATE_INT);
        if ($price === false || $price <= 0) {
            $errors['price'] = 'Giá phải là số nguyên dương.';
        }
        $data['price'] = $price === false ? 0 : $price;

        $discountPrice = trim((string) ($post['discount_price'] ?? ''));
        if ($discountPrice !== '') {
            $discountPrice = filter_var($discountPrice, FILTER_VALIDATE_INT);
            if ($discountPrice === false || $discountPrice <= 0 || $discountPrice > $data['price']) {
                $errors['discount_price'] = 'Giá khuyến mãi phải lớn hơn 0 và không vượt giá niêm yết.';
            }
        }
        $data['discount_price'] = $discountPrice === '' || $discountPrice === false ? null : $discountPrice;

        $condition = (string) ($post['condition_type'] ?? 'new');
        if (!in_array($condition, ['new', 'used'], true)) {
            $errors['condition_type'] = 'Tình trạng xe không hợp lệ.';
            $condition = 'new';
        }
        $data['condition_type'] = $condition;

        $carCode = strtoupper(trim((string) ($post['car_code'] ?? '')));
        if ($carCode !== '' && !preg_match('/^[A-Z0-9-]{3,40}$/', $carCode)) {
            $errors['car_code'] = 'Mã xe chỉ gồm chữ in hoa, số và dấu gạch ngang.';
        }
        if ($carCode !== '' && (new Car())->carCodeExists($carCode, $excludeId)) {
            $errors['car_code'] = 'Mã xe này đã được sử dụng.';
        }
        $data['car_code'] = $carCode;

        $galleryLines = preg_split('/\r\n|\r|\n/', trim((string) ($post['gallery_urls'] ?? ''))) ?: [];
        $gallery = [];
        foreach ($galleryLines as $galleryUrl) {
            $galleryUrl = trim($galleryUrl);
            if ($galleryUrl === '') {
                continue;
            }
            if (
                mb_strlen($galleryUrl) > 500
                || !(preg_match('#^https://#i', $galleryUrl) || preg_match('#^uploads/cars/[a-zA-Z0-9._-]+$#', $galleryUrl))
            ) {
                $errors['gallery_urls'] = 'Mỗi ảnh cần là đường dẫn https:// hoặc uploads/cars/tên-tệp.';
                break;
            }
            $gallery[] = $galleryUrl;
        }
        $data['gallery_json'] = json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $videoUrl = trim((string) ($post['video_url'] ?? ''));
        if ($videoUrl !== '' && (!filter_var($videoUrl, FILTER_VALIDATE_URL) || !preg_match('#^https://#i', $videoUrl))) {
            $errors['video_url'] = 'Video cần có đường dẫn HTTPS hợp lệ.';
        }
        $data['video_url'] = $videoUrl;

        $detailsJson = trim((string) ($post['details_json'] ?? ''));
        if ($detailsJson === '') {
            $detailsJson = '{}';
        }
        $details = json_decode($detailsJson, true);
        if (!is_array($details) || json_last_error() !== JSON_ERROR_NONE) {
            $errors['details_json'] = 'Thông tin kỹ thuật cần là JSON hợp lệ.';
        } else {
            foreach (['variants', 'options'] as $group) {
                if (!isset($details[$group])) {
                    continue;
                }
                if (!is_array($details[$group])) {
                    $errors['details_json'] = 'Các nhóm phiên bản và tùy chọn phải là danh sách.';
                    break;
                }
                foreach ($details[$group] as $entry) {
                    $entryPrice = is_array($entry)
                        ? filter_var($entry['price'] ?? 0, FILTER_VALIDATE_INT)
                        : false;
                    if (
                        !is_array($entry)
                        || !is_string($entry['name'] ?? null)
                        || trim($entry['name']) === ''
                        || mb_strlen($entry['name']) > 100
                        || $entryPrice === false
                        || $entryPrice < 0
                    ) {
                        $errors['details_json'] = 'Mỗi phiên bản/tùy chọn cần có tên và giá chênh lệch là số nguyên không âm.';
                        break 2;
                    }
                }
            }
        }
        $data['details_json'] = $detailsJson;

        $statuses = ['available', 'reserved', 'sold', 'hidden'];
        $status = trim((string) ($post['status'] ?? 'available'));
        if (!in_array($status, $statuses, true)) {
            $status = 'available';
        }
        $data['status'] = $status;

        $data['featured'] = isset($post['featured']) ? 1 : 0;
        $data['badge'] = trim((string) ($post['badge'] ?? ''));
        if (mb_strlen($data['badge']) > 80) {
            $errors['badge'] = 'Badge tối đa 80 ký tự.';
        }
        $data['description'] = trim((string) ($post['description'] ?? ''));

        if ($requireImage && empty($files['image']['tmp_name'])) {
            $errors['image'] = 'Vui lòng chọn ảnh xe.';
        }
        if (!empty($files['image']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['image']['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $errors['image'] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.';
            }
            if (($files['image']['size'] ?? 0) > 5 * 1024 * 1024) {
                $errors['image'] = 'Ảnh tối đa 5MB.';
            }
            if (($files['image']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $errors['image'] = 'Tải ảnh thất bại.';
            }
        }

        return $data;
    }

    private function renderCarForm(array $data): void
    {
        $catalog = new CatalogOption();
        $this->render('admin/cars/form', $data + [
            'brands' => $catalog->values('brand'),
            'bodyTypes' => $catalog->values('body_type'),
        ]);
    }

    private function handleUpload(array $file, array &$errors): ?string
    {
        if (empty($file['tmp_name'])) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
        if ($ext === null) {
            $errors['image'] = 'Định dạng ảnh không hợp lệ.';
            return null;
        }
        $dir = dirname(__DIR__, 3) . '/public/uploads/cars';
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $errors['image'] = 'Không thể tạo thư mục upload.';
            return null;
        }
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors['image'] = 'Không thể lưu ảnh. Vui lòng thử lại.';
            return null;
        }
        return 'uploads/cars/' . $filename;
    }

    private function deleteImageFile(string $path): void
    {
        if (preg_match('#^https?://#i', $path)) {
            return;
        }
        $full = dirname(__DIR__, 3) . '/public/' . ltrim($path, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return is_string($message) ? $message : null;
    }
}
