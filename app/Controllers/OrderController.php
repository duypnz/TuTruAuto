<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\CustomerOrder;
use DateTimeImmutable;

final class OrderController extends Controller
{
    public function customerOrders(): void
    {
        if (!$this->requireCustomer()) {
            return;
        }
        $this->render('account/orders', [
            'title' => 'Đơn đặt cọc',
            'orders' => (new CustomerOrder())->findForCustomer((int) $_SESSION['user']['id']),
            'success' => $this->takeFlash('_success'),
            'error' => $this->takeFlash(),
        ]);
    }

    public function customerOrderDetail(array $params): void
    {
        if (!$this->requireCustomer()) {
            return;
        }
        $order = (new CustomerOrder())->findForCustomerById(
            (int) $_SESSION['user']['id'],
            (int) ($params['id'] ?? 0)
        );
        if (!$order) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->render('account/order-show', [
            'title' => 'Chi tiết đơn đặt cọc #' . $order['id'],
            'order' => $order,
            'success' => $this->takeFlash('_success'),
        ]);
    }

    public function adminOrders(): void
    {
        $this->requireAdmin();
        $filters = [
            'keyword' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'from' => trim((string) ($_GET['from'] ?? '')),
            'to' => trim((string) ($_GET['to'] ?? '')),
        ];
        $error = null;
        foreach (['from', 'to'] as $dateField) {
            if ($filters[$dateField] !== '' && !$this->validDate($filters[$dateField])) {
                $filters[$dateField] = '';
                $error = 'Ngày lọc không hợp lệ. Vui lòng nhập lại ngày theo lịch.';
            }
        }
        if (
            $filters['from'] !== '' && $filters['to'] !== ''
            && $filters['from'] > $filters['to']
        ) {
            $filters['to'] = '';
            $error = 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.';
        }

        $this->render('admin/orders/index', [
            'title' => 'Quản lý đơn đặt cọc',
            'orders' => (new CustomerOrder())->searchForAdmin($filters),
            'filters' => $filters,
            'flash' => $this->takeFlash(),
            'error' => $error,
        ]);
    }

    public function updateOrder(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $status = (string) ($_POST['status'] ?? '');
        $statuses = ['pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled'];
        if (!in_array($status, $statuses, true)) {
            http_response_code(400);
            echo 'Trạng thái đơn hàng không hợp lệ.';
            return;
        }

        $deliveryInput = trim((string) ($_POST['delivery_at'] ?? ''));
        $deliveryAt = null;
        if ($deliveryInput !== '') {
            $deliveryDate = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $deliveryInput);
            $dateErrors = DateTimeImmutable::getLastErrors();
            if (
                !$deliveryDate
                || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
                || $deliveryDate->format('Y-m-d\TH:i') !== $deliveryInput
            ) {
                $_SESSION['_flash'] = 'Ngày và giờ nhận xe không hợp lệ.';
                $this->redirect('/admin/don-hang');
            }
            $deliveryAt = $deliveryDate->format('Y-m-d H:i:s');
        }

        $updated = (new CustomerOrder())->updateManagementDetails(
            (int) ($params['id'] ?? 0),
            $status,
            $deliveryAt
        );
        $_SESSION['_flash'] = $updated
            ? 'Đã cập nhật trạng thái và lịch nhận xe.'
            : 'Không tìm thấy đơn hàng cần cập nhật.';
        $this->redirect('/admin/don-hang');
    }

    private function validDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
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
