<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Exceptions\VoucherException;
use App\Models\Car;
use App\Models\CatalogOption;
use App\Models\CustomerInquiry;
use App\Models\CustomerOrder;
use App\Models\SiteSetting;
use App\Services\CheckoutService;
use App\Services\EmailService;
use App\Services\VoucherService;
use Throwable;

final class CarController extends Controller
{
    public function index(): void
    {
        $catalog = new CatalogOption();
        $carModel = new Car();
        $filters = [
            'keyword' => trim((string) ($_GET['q'] ?? '')),
            'brand' => trim((string) ($_GET['brand'] ?? '')),
            'body_type' => trim((string) ($_GET['body_type'] ?? '')),
            'fuel' => trim((string) ($_GET['fuel'] ?? '')),
            'sort' => (string) ($_GET['sort'] ?? ''),
        ];
        $totalCars = $carModel->countSearch($filters);
        $perPage = 9;
        $totalPages = max(1, (int) ceil($totalCars / $perPage));
        $requestedPage = filter_var(
            is_scalar($_GET['page'] ?? null) ? (string) $_GET['page'] : null,
            FILTER_VALIDATE_INT
        );
        $currentPage = min(
            $requestedPage === false ? 1 : max(1, $requestedPage),
            $totalPages
        );
        $cars = $carModel->search($filters, $perPage, ($currentPage - 1) * $perPage);

        $this->render('cars/index', [
            'cars' => $cars,
            'brands' => $catalog->values('brand'),
            'bodyTypes' => $catalog->values('body_type'),
            'filters' => $filters,
            'totalCars' => $totalCars,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
        ]);
    }

    public function compare(): void
    {
        $rawIds = $_GET['ids'] ?? [];
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $ids = is_array($rawIds) ? array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): int => is_scalar($id) ? (int) $id : 0, $rawIds),
            static fn (int $id): bool => $id > 0
        ))) : [];
        $selectedIds = array_slice($ids, 0, 3);
        $cars = new Car();
        $selectedCars = $cars->findByIds($selectedIds);
        $this->render('cars/compare', [
            'title' => 'So sánh xe',
            'selectedCars' => $selectedCars,
            'availableCars' => $cars->search([]),
            'selectedIds' => array_map(static fn (array $car): int => (int) $car['id'], $selectedCars),
        ]);
    }

    public function show(array $params): void
    {
        $car = (new Car())->findBySlug((string) $params['slug']);

        if (!$car) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $car['details'] = $this->decodeJson($car['details_json'] ?? null, []);
        $car['gallery'] = $this->decodeJson($car['gallery_json'] ?? null, []);
        $car['gallery'] = array_values(array_filter(
            $car['gallery'],
            static fn ($image): bool => is_string($image) && trim($image) !== ''
        ));
        $mainImage = trim((string) $car['image_url']);
        $car['gallery'] = array_values(array_filter(
            $car['gallery'],
            static fn (string $image): bool => trim($image) !== $mainImage
        ));
        array_unshift($car['gallery'], $mainImage);
        $this->render('cars/show', [
            'car' => $car,
            'relatedCars' => (new Car())->related($car),
            'reviews' => (new Car())->reviews((int) $car['id']),
            'reviewSummary' => (new Car())->reviewSummary((int) $car['id']),
            'csrf' => Csrf::token(),
            'isAdmin' => ($_SESSION['user']['role'] ?? '') === 'admin',
            'success' => $this->takeFlash('_success'),
            'error' => $this->takeFlash(),
        ]);
    }

    public function submitCarRequest(array $params): void
    {
        $cars = new Car();
        $car = $cars->findBySlug((string) ($params['slug'] ?? ''));
        if (!$car || !Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code($car ? 419 : 404);
            echo $car ? 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.' : 'Không tìm thấy xe.';
            return;
        }

        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'deposit' && !$this->requireCustomer()) {
            return;
        }
        if (!in_array($action, ['deposit', 'test_drive', 'quote', 'advice'], true)) {
            http_response_code(400);
            echo 'Yêu cầu không hợp lệ.';
            return;
        }
        if ($action === 'deposit' && $car['status'] === 'hidden') {
            $_SESSION['_flash'] = 'Mẫu xe này hiện không nhận đặt cọc.';
            $this->redirect('/xe/' . rawurlencode((string) $car['slug']));
        }

        $details = $this->decodeJson($car['details_json'] ?? null, []);
        $selection = $this->selectedOptions($details, $_POST);
        $unitPrice = (int) ($car['discount_price'] ?: $car['price']) + $selection['delta'];
        $quantity = 1;
        $voucherCode = strtoupper(trim((string) ($_POST['voucher_code'] ?? '')));
        $userId = isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'customer'
            ? (int) $_SESSION['user']['id']
            : null;
        $discount = 0;
        if ($voucherCode !== '') {
            $quote = (new VoucherService())->quote(
                $voucherCode,
                $unitPrice * $quantity,
                $userId,
                (string) $car['brand'],
                (string) $car['slug']
            );
            if (!$quote['valid']) {
                $_SESSION['_flash'] = $quote['message'];
                $this->redirect('/xe/' . rawurlencode((string) $car['slug']));
            }
            $discount = $quote['discount'];
        }

        $types = [
            'test_drive' => 'test_drive',
            'quote' => 'quote',
            'advice' => 'advice',
        ];

        $name = trim((string) ($_POST['customer_name'] ?? $_SESSION['user']['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? $_SESSION['user']['email'] ?? '')));
        $message = trim((string) ($_POST['message'] ?? ''));
        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 120
            || !preg_match('/^[0-9+().\s-]{7,30}$/', $phone)
            || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
        ) {
            $_SESSION['_flash'] = 'Vui lòng nhập họ tên, điện thoại và email hợp lệ.';
            $this->redirect('/xe/' . rawurlencode((string) $car['slug']));
        }

        $description = implode(', ', $selection['labels']);
        $description .= ($description !== '' ? '. ' : '') . 'Số lượng: ' . $quantity
            . '; giá dự kiến: ' . number_format(($unitPrice * $quantity) - $discount, 0, ',', '.') . ' VND.'
            . ($voucherCode !== '' ? '; voucher: ' . $voucherCode : '')
            . ($message !== '' ? ' ' . $message : '');
        if ($action === 'deposit') {
            $paymentMethod = (string) ($_POST['payment_method'] ?? '');
            if (!in_array($paymentMethod, ['bank', 'cash'], true)) {
                $paymentMethod = 'bank';
            }
            $paymentDetails = (new SiteSetting())->paymentDetails();
            $paymentDetails['method'] = $paymentMethod;
            $stockQuantity = isset($car['stock_quantity']) ? (int) $car['stock_quantity'] : null;
            $inStock = $stockQuantity !== null && $stockQuantity > 0;
            $paymentDetails['inventory_status'] = $stockQuantity === null
                ? 'unverified'
                : ($inStock ? 'in_stock' : 'preorder');
            $paymentDetails['lead_time_days'] = max(1, (int) ($car['lead_time_days'] ?? 21));
            if ($stockQuantity === 0) {
                $paymentDetails['estimated_delivery_date'] = (new \DateTimeImmutable())
                    ->modify('+' . $paymentDetails['lead_time_days'] . ' days')
                    ->format('Y-m-d');
            }
            $orderData = [
                'user_id' => $userId,
                'order_type' => 'deposit',
                'status' => 'deposit_pending',
                'customer_name' => $name,
                'phone' => $phone,
                'message' => $description,
                'voucher_code' => $voucherCode,
                'subtotal' => $unitPrice * $quantity,
                'discount_total' => $discount,
                'total' => ($unitPrice * $quantity) - $discount,
                'deposit_amount' => max(1, (int) round((($unitPrice * $quantity) - $discount) * 0.1)),
                'delivery_at' => null,
                'payment_info' => $paymentDetails,
            ];
            $orderItems = [[
                'car_id' => (int) $car['id'],
                'name' => (string) $car['name'],
                'image_url' => (string) $car['image_url'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'options' => $selection['labels'],
            ]];
            try {
                $orderId = (new CheckoutService())->createDeposit(
                    $orderData,
                    $orderItems,
                    $voucherCode,
                    $userId,
                    (string) $car['brand'],
                    (string) $car['slug']
                );
            } catch (VoucherException $error) {
                $_SESSION['_flash'] = 'Mã giảm giá không còn hợp lệ: ' . $error->getMessage();
                $this->redirect('/xe/' . rawurlencode((string) $car['slug']));
            }
            $emailSent = true;
            try {
                $createdOrder = (new CustomerOrder())->findForCustomerById((int) $userId, $orderId);
                if ($createdOrder === null) {
                    throw new \RuntimeException('Không tìm thấy đơn hàng vừa tạo để gửi email.');
                }
                (new EmailService())->sendOrderConfirmation($createdOrder);
            } catch (Throwable $error) {
                $emailSent = false;
                error_log(sprintf(
                    'Order confirmation email failed for order #%d: %s',
                    $orderId,
                    $error->getMessage()
                ));
            }
            $_SESSION['_success'] = match ($paymentDetails['inventory_status']) {
                'in_stock' => 'Đã tạo yêu cầu đặt cọc #' . $orderId . '. Tiền cọc bằng 10% giá xe.',
                'preorder' => 'Đã tạo yêu cầu đặt cọc trước #' . $orderId . '. Showroom sẽ xác nhận thời gian nhận xe dự kiến.',
                default => 'Đã tạo yêu cầu #' . $orderId . '. Showroom sẽ kiểm tra tồn kho trước khi xác nhận nhận cọc.',
            };
            if (!$emailSent) {
                $_SESSION['_success'] .= ' Đơn hàng đã được lưu nhưng email xác nhận chưa gửi được.';
            }
            $this->redirect('/tai-khoan/don-hang/' . $orderId);
        }
        (new CustomerInquiry())->create(
            (int) $car['id'],
            $userId,
            $types[$action],
            $name,
            $phone,
            $email,
            $description
        );
        $_SESSION['_success'] = match ($action) {
            'test_drive' => 'Đã nhận đăng ký lái thử. Nhân viên sẽ liên hệ xác nhận lịch.',
            'quote' => 'Đã nhận yêu cầu báo giá. Chúng tôi sẽ liên hệ sớm.',
            'advice' => 'Đã gửi yêu cầu tư vấn. Chúng tôi sẽ gọi lại cho bạn.',
            default => 'Đã gửi yêu cầu tư vấn.',
        };
        $this->redirect('/xe/' . rawurlencode((string) $car['slug']));
    }

    public function submitContact(): void
    {
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }
        $name = trim((string) ($_POST['customer_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $message = trim((string) ($_POST['message'] ?? ''));
        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 120
            || !preg_match('/^[0-9+().\s-]{7,30}$/', $phone)
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || mb_strlen($message) > 2000
        ) {
            $_SESSION['_flash'] = 'Vui lòng kiểm tra họ tên, số điện thoại, email và nội dung liên hệ.';
            $this->redirect('/#lien-he');
        }

        $userId = isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'customer'
            ? (int) $_SESSION['user']['id']
            : null;
        (new CustomerInquiry())->create(null, $userId, 'contact', $name, $phone, $email, $message);
        $_SESSION['_success'] = 'Đã nhận thông tin liên hệ. Nhân viên sẽ gọi lại sớm.';
        $this->redirect('/#lien-he');
    }

    public function adviceForm(): void
    {
        $this->render('advice/index', [
            'title' => 'Đặt lịch tư vấn',
            'success' => $this->takeFlash('_success'),
            'error' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function submitAdvice(): void
    {
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }
        $name = trim((string) ($_POST['customer_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $carInterest = trim((string) ($_POST['car_interest'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 120
            || !preg_match('/^[0-9+().\s-]{7,30}$/', $phone)
            || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
            || mb_strlen($message) > 2000
        ) {
            $_SESSION['_flash'] = 'Vui lòng nhập họ tên, số điện thoại, email và nội dung hợp lệ.';
            $this->redirect('/tu-van');
        }
        $description = ($carInterest !== '' ? 'Xe quan tâm: ' . $carInterest . '. ' : '') . $message;
        $userId = isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'customer'
            ? (int) $_SESSION['user']['id']
            : null;
        (new CustomerInquiry())->create(null, $userId, 'advice', $name, $phone, $email, $description);
        $_SESSION['_success'] = 'Đã gửi yêu cầu tư vấn. Chuyên viên Tu Tru Auto sẽ liên hệ trong giờ làm việc.';
        $this->redirect('/tu-van');
    }

    public function submitReview(array $params): void
    {
        if (!$this->requireCustomer()) {
            return;
        }
        $slug = (string) ($params['slug'] ?? '');
        $cars = new Car();
        $car = $cars->findBySlug($slug);
        if (!$car) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $_SESSION['_flash'] = 'Phiên làm việc đã hết hạn. Vui lòng gửi lại đánh giá.';
            $this->redirect('/xe/' . rawurlencode($slug));
        }
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($rating === false || $rating < 1 || $rating > 5 || mb_strlen($content) < 5 || mb_strlen($content) > 2000) {
            $_SESSION['_flash'] = 'Vui lòng chọn số sao và nhập nhận xét từ 5 đến 2.000 ký tự.';
            $this->redirect('/xe/' . rawurlencode($slug) . '#danh-gia');
        }
        $cars->addReview((int) $car['id'], (int) $_SESSION['user']['id'], $rating, $content);
        $_SESSION['_success'] = 'Cảm ơn bạn đã chia sẻ đánh giá.';
        $this->redirect('/xe/' . rawurlencode($slug) . '#danh-gia');
    }

    public function checkVoucher(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo json_encode(['valid' => false, 'message' => 'Phiên làm việc đã hết hạn.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $code = trim((string) ($_POST['code'] ?? ''));
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_INT);
        $slug = trim((string) ($_POST['car_slug'] ?? ''));
        if ($code === '' || $price === false || $price < 0) {
            http_response_code(400);
            echo json_encode(['valid' => false, 'message' => 'Vui lòng nhập mã và chọn phiên bản trước.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $car = $slug !== '' ? (new Car())->findBySlug($slug) : null;
        $userId = isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'customer'
            ? (int) $_SESSION['user']['id']
            : null;
        $result = (new VoucherService())->quote(
            $code,
            $price,
            $userId,
            $car ? (string) $car['brand'] : null,
            $car ? (string) $car['slug'] : null
        );
        echo json_encode([
            'valid' => $result['valid'],
            'code' => $result['code'],
            'discount' => $result['discount'],
            'message' => $result['message'],
        ], JSON_UNESCAPED_UNICODE);
    }

    public function favorites(): void
    {
        if (!$this->requireCustomer()) {
            return;
        }

        $favorites = (int) ($_SESSION['favorites_user_id'] ?? 0)
            === (int) $_SESSION['user']['id']
            ? ($_SESSION['favorites'] ?? [])
            : [];

        if (!is_array($favorites)) {
            $favorites = [];
        }

        $cars = (new Car())->findBySlugs($favorites);

        $this->render('favorites/index', ['cars' => $cars]);
    }

    public function toggleFavorite(array $params): void
    {
        if (!$this->requireCustomer()) {
            return;
        }

        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            return;
        }

        $slug = trim((string) ($params['slug'] ?? ''));

        if ($slug === '' || !(new Car())->findBySlug($slug)) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $favorites = (int) ($_SESSION['favorites_user_id'] ?? 0)
            === (int) $_SESSION['user']['id']
            ? ($_SESSION['favorites'] ?? [])
            : [];

        if (!is_array($favorites)) {
            $favorites = [];
        }

        $index = array_search($slug, $favorites, true);

        if ($index === false) {
            $favorites[] = $slug;
        } else {
            unset($favorites[$index]);
            $favorites = array_values($favorites);
        }

        $_SESSION['favorites'] = $favorites;
        $_SESSION['favorites_user_id'] = (int) $_SESSION['user']['id'];

        $this->redirect($this->redirectTarget());
    }

    private function requireCustomer(): bool
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/dang-nhap');
            return false;
        }

        if (
            ($_SESSION['user']['role'] ?? '') !== 'customer'
            || !isset($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            $this->render('errors/403');
            return false;
        }

        return true;
    }

    private function redirectTarget(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $parsed = $referer !== '' ? parse_url($referer) : false;

        if (!is_array($parsed) || empty($parsed['path'])) {
            return '/';
        }

        $path = '/' . ltrim((string) $parsed['path'], '/');

        $base = rtrim(
            str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')),
            '/'
        );

        if (
            $base !== ''
            && $base !== '/'
            && ($path === $base || str_starts_with($path, $base . '/'))
        ) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        $query = isset($parsed['query']) && $parsed['query'] !== ''
            ? '?' . $parsed['query']
            : '';

        return $path . $query;
    }

    private function selectedOptions(array $details, array $input): array
    {
        $labels = [];
        $delta = 0;
        $variants = is_array($details['variants'] ?? null) ? $details['variants'] : [];
        if ($variants === []) {
            $variants = [
                ['name' => 'Base', 'price' => 0],
                ['name' => 'Premium', 'price' => 150000000],
                ['name' => 'Luxury', 'price' => 300000000],
            ];
        }
        $selectedVariant = trim((string) ($input['variant'] ?? ''));
        foreach ($variants as $variant) {
            if (is_array($variant) && ($variant['name'] ?? '') === $selectedVariant) {
                $labels[] = $selectedVariant;
                $delta += (int) ($variant['price'] ?? 0);
                break;
            }
        }
        foreach (['exterior' => 'Màu ngoại thất', 'interior' => 'Màu nội thất'] as $field => $label) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value !== '' && mb_strlen($value) <= 60) {
                $labels[] = $label . ': ' . $value;
            }
        }
        $options = is_array($details['options'] ?? null) ? $details['options'] : [];
        $selectedOptions = $input['options'] ?? [];
        if (is_array($selectedOptions)) {
            foreach ($options as $option) {
                if (
                    is_array($option)
                    && in_array((string) ($option['name'] ?? ''), $selectedOptions, true)
                ) {
                    $labels[] = (string) $option['name'];
                    $delta += (int) ($option['price'] ?? 0);
                }
            }
        }
        return ['labels' => $labels, 'delta' => $delta];
    }

    private function decodeJson(mixed $value, array $fallback): array
    {
        if (!is_string($value) || $value === '') {
            return $fallback;
        }
        try {
            $decoded = json_decode($value, true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $fallback;
        }
        return is_array($decoded) ? $decoded : $fallback;
    }

    private function takeFlash(string $key = '_flash'): ?string
    {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return is_string($message) ? $message : null;
    }
}
