<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Exceptions\OrderException;
use App\Models\CustomerOrder;
<<<<<<< HEAD
use App\Services\EmailService;
=======
use App\Models\OrderStatusHistory;
use App\Services\OrderManagementService;
>>>>>>> 20594bd5d59376df6f81c1a97ec32066787a3343
use DateTimeImmutable;
use Throwable;

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
            'history' => (new OrderStatusHistory())->forOrder((int) $order['id']),
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

        $orders = (new CustomerOrder())->searchForAdmin($filters);
        $this->render('admin/orders/index', [
            'title' => 'Quản lý đơn đặt cọc',
            'orders' => $orders,
            'histories' => (new OrderStatusHistory())->forOrders(array_column($orders, 'id')),
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

        $deliveryAt = $this->parseDeliveryAt((string) ($_POST['delivery_at'] ?? ''));
        if ($deliveryAt === false) {
            $_SESSION['_flash'] = 'Ngày và giờ nhận xe không hợp lệ.';
            $this->redirect('/admin/don-hang');
        }

        try {
            (new OrderManagementService())->updateDetails(
                (int) ($params['id'] ?? 0),
                (string) ($_POST['status'] ?? ''),
                $deliveryAt,
                $this->actorId(),
                $this->actorName()
            );
            $_SESSION['_flash'] = 'Đã cập nhật trạng thái và lịch nhận xe.';
        } catch (OrderException $error) {
            $_SESSION['_flash'] = $error->getMessage();
        }
        $this->redirect('/admin/don-hang');
    }

    public function verifyPayment(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        try {
            (new OrderManagementService())->verifyPayment(
                (int) ($params['id'] ?? 0),
                (string) ($_POST['payment_reference'] ?? ''),
                (string) ($_POST['payment_note'] ?? ''),
                isset($_POST['verified']),
                $this->actorId(),
                $this->actorName()
            );
            $_SESSION['_flash'] = isset($_POST['verified'])
                ? 'Đã xác nhận tiền cọc và ghi nhận thông tin đối soát.'
                : 'Đã hủy xác nhận tiền cọc.';
        } catch (OrderException $error) {
            $_SESSION['_flash'] = $error->getMessage();
        }
        $this->redirect('/admin/don-hang');
    }

    public function cancelOrder(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

<<<<<<< HEAD
        $update = (new CustomerOrder())->updateManagementDetails(
            (int) ($params['id'] ?? 0),
            $status,
            $deliveryAt
        );
        if ($update === null) {
            $_SESSION['_flash'] = 'Không tìm thấy đơn hàng cần cập nhật.';
            $this->redirect('/admin/don-hang');
        }

        $_SESSION['_flash'] = 'Đã cập nhật trạng thái và lịch nhận xe.';
        if ($update['status_changed']) {
            try {
                (new EmailService())->sendOrderStatusUpdate($update['order']);
            } catch (Throwable $error) {
                error_log(sprintf(
                    'Order status email failed for order #%d: %s',
                    (int) $params['id'],
                    $error->getMessage()
                ));
                $_SESSION['_flash'] .= ' Trạng thái đã lưu nhưng email thông báo chưa gửi được.';
            }
=======
        try {
            (new OrderManagementService())->cancel(
                (int) ($params['id'] ?? 0),
                (string) ($_POST['cancel_reason'] ?? ''),
                $this->actorId(),
                $this->actorName()
            );
            $_SESSION['_flash'] = 'Đã hủy đơn. Nếu đã nhận cọc, hãy thực hiện hoàn cọc.';
        } catch (OrderException $error) {
            $_SESSION['_flash'] = $error->getMessage();
>>>>>>> 20594bd5d59376df6f81c1a97ec32066787a3343
        }
        $this->redirect('/admin/don-hang');
    }

    public function refundDeposit(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $amount = filter_var($_POST['refund_amount'] ?? null, FILTER_VALIDATE_INT);
        try {
            (new OrderManagementService())->refund(
                (int) ($params['id'] ?? 0),
                $amount === false ? 0 : $amount,
                (string) ($_POST['refund_method'] ?? ''),
                (string) ($_POST['refund_note'] ?? ''),
                $this->actorId(),
                $this->actorName()
            );
            $_SESSION['_flash'] = 'Đã ghi nhận hoàn cọc.';
        } catch (OrderException $error) {
            $_SESSION['_flash'] = $error->getMessage();
        }
        $this->redirect('/admin/don-hang');
    }

    private function parseDeliveryAt(string $input): string|false|null
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $input);
        $errors = DateTimeImmutable::getLastErrors();
        if (
            !$date
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d\TH:i') !== $input
        ) {
            return false;
        }

        return $date->format('Y-m-d H:i:s');
    }

    private function actorId(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    private function actorName(): ?string
    {
        return isset($_SESSION['user']['name']) ? (string) $_SESSION['user']['name'] : null;
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
