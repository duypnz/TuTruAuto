<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\CustomerOrder;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->render('cart/index', [
            'title' => 'Giỏ hàng',
            'cart' => $this->cart(),
            'csrf' => Csrf::token(),
            'success' => $this->takeFlash('_success'),
            'error' => $this->takeFlash(),
        ]);
    }

    public function update(): void
    {
        if (!$this->validPost()) {
            return;
        }
        $key = (string) ($_POST['key'] ?? '');
        $cart = $this->cart();
        if (!isset($cart[$key])) {
            $_SESSION['_flash'] = 'Sản phẩm không còn trong giỏ hàng.';
            $this->redirect('/gio-hang');
        }
        if (($_POST['action'] ?? '') === 'remove') {
            unset($cart[$key]);
            $_SESSION['_success'] = 'Đã xóa xe khỏi giỏ hàng.';
        } else {
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($quantity === false || $quantity < 1 || $quantity > 5) {
                $_SESSION['_flash'] = 'Số lượng xe phải từ 1 đến 5.';
                $this->redirect('/gio-hang');
            }
            $cart[$key]['quantity'] = $quantity;
            $voucherCode = (string) ($cart[$key]['voucher_code'] ?? '');
            $voucher = $voucherCode !== '' ? (new \App\Models\Car())->voucher($voucherCode) : null;
            $subtotal = (int) $cart[$key]['unit_price'] * $quantity;
            if ($voucher && $subtotal >= (int) $voucher['minimum_order']) {
                $cart[$key]['discount_total'] = $voucher['discount_type'] === 'percent'
                    ? min($subtotal, (int) round($subtotal * (float) $voucher['discount_value'] / 100))
                    : min($subtotal, (int) $voucher['discount_value']);
            } else {
                $cart[$key]['discount_total'] = 0;
                if ($voucherCode !== '') {
                    $cart[$key]['voucher_code'] = '';
                }
            }
            $_SESSION['_success'] = 'Đã cập nhật giỏ hàng.';
        }
        $_SESSION['cart'] = $cart;
        $this->redirect('/gio-hang');
    }

    public function checkout(): void
    {
        if (!$this->validPost()) {
            return;
        }
        $cart = $this->cart();
        if ($cart === []) {
            $_SESSION['_flash'] = 'Giỏ hàng đang trống.';
            $this->redirect('/gio-hang');
        }
        $carModel = new \App\Models\Car();
        foreach ($cart as $key => $item) {
            $currentCar = $carModel->findById((int) $item['car_id']);
            if (!$currentCar || $currentCar['status'] !== 'available') {
                unset($cart[$key]);
            }
        }
        if (count($cart) !== count($_SESSION['cart'] ?? [])) {
            $_SESSION['cart'] = $cart;
            $_SESSION['_flash'] = 'Một hoặc nhiều xe đã hết hàng và được xóa khỏi giỏ. Vui lòng kiểm tra lại.';
            $this->redirect('/gio-hang');
        }
        $name = trim((string) ($_POST['customer_name'] ?? $_SESSION['user']['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $type = (string) ($_POST['order_type'] ?? '');
        $message = trim((string) ($_POST['message'] ?? ''));
        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 120
            || !preg_match('/^[0-9+().\s-]{7,30}$/', $phone)
            || !in_array($type, ['purchase', 'deposit'], true)
        ) {
            $_SESSION['_flash'] = 'Vui lòng nhập họ tên, số điện thoại hợp lệ và chọn mua xe hoặc đặt cọc.';
            $this->redirect('/gio-hang');
        }

        $items = array_values($cart);
        $subtotal = 0;
        $discount = 0;
        foreach ($items as $item) {
            $subtotal += (int) $item['unit_price'] * (int) $item['quantity'];
            $discount += (int) ($item['discount_total'] ?? 0);
        }
        $discount = min($subtotal, $discount);
        $voucherCodes = array_values(array_unique(array_filter(array_map(
            static fn (array $item): string => (string) ($item['voucher_code'] ?? ''),
            $items
        ))));
        $voucherCode = count($voucherCodes) === 1 ? $voucherCodes[0] : (count($voucherCodes) > 1 ? 'NHIỀU MÃ' : '');
        if ($voucherCode !== '' && $voucherCode !== 'NHIỀU MÃ' && trim((string) ($_POST['voucher_code'] ?? '')) !== $voucherCode) {
            $_SESSION['_flash'] = 'Mã voucher không khớp với giỏ hàng.';
            $this->redirect('/gio-hang');
        }
        $orderId = (new CustomerOrder())->create([
            'user_id' => isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'customer'
                ? (int) $_SESSION['user']['id']
                : null,
            'order_type' => $type,
            'status' => $type === 'deposit' ? 'deposit_pending' : 'pending',
            'customer_name' => $name,
            'phone' => $phone,
            'message' => $message,
            'voucher_code' => $voucherCode,
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'total' => $subtotal - $discount,
        ], $items);
        unset($_SESSION['cart']);
        $_SESSION['_success'] = 'Đã ghi nhận đơn #' . $orderId . '. '
            . ($type === 'deposit'
                ? 'Trạng thái hiện tại: chờ xác nhận đặt cọc.'
                : 'Trạng thái hiện tại: chờ xác nhận mua xe.');
        $this->redirect(
            isset($_SESSION['user'])
            && ($_SESSION['user']['role'] ?? '') === 'customer'
                ? '/tai-khoan/don-hang'
                : '/gio-hang'
        );
    }

    public function customerOrders(): void
    {
        if (!$this->requireCustomer()) {
            return;
        }
        $this->render('account/orders', [
            'title' => 'Đơn mua xe',
            'orders' => (new CustomerOrder())->findForCustomer((int) $_SESSION['user']['id']),
            'success' => $this->takeFlash('_success'),
            'error' => $this->takeFlash(),
        ]);
    }

    public function adminOrders(): void
    {
        $this->requireAdmin();
        $this->render('admin/orders/index', [
            'title' => 'Đơn mua xe',
            'orders' => (new CustomerOrder())->allForAdmin(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function updateOrderStatus(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }
        $statuses = ['pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled'];
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, $statuses, true)) {
            http_response_code(400);
            echo 'Trạng thái đơn hàng không hợp lệ.';
            return;
        }
        $updated = (new CustomerOrder())->updateStatus((int) ($params['id'] ?? 0), $status);
        $_SESSION['_flash'] = $updated
            ? 'Đã cập nhật trạng thái đơn hàng.'
            : 'Không tìm thấy đơn hàng cần cập nhật.';
        $this->redirect('/admin/don-hang');
    }

    private function cart(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            return [];
        }
        $available = [];
        foreach ($cart as $key => $item) {
            if (
                is_string($key) && is_array($item)
                && isset($item['car_id'], $item['name'], $item['slug'], $item['image_url'], $item['quantity'], $item['unit_price'])
            ) {
                $available[$key] = $item;
            }
        }
        $_SESSION['cart'] = $available;

        return $available;
    }

    private function validPost(): bool
    {
        if (Csrf::valid($_POST['_csrf'] ?? null)) {
            return true;
        }
        http_response_code(419);
        $_SESSION['_flash'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
        $this->redirect('/gio-hang');
    }

    private function requireCustomer(): bool
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/dang-nhap');
            return false;
        }
        if (($_SESSION['user']['role'] ?? '') !== 'customer' || !isset($_SESSION['user']['id'])) {
            http_response_code(403);
            $this->render('errors/403');
            return false;
        }
        return true;
    }

    private function takeFlash(string $key = '_flash'): ?string
    {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return is_string($message) ? $message : null;
    }
}
