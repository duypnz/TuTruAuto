<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\SiteSetting;

final class SettingsController extends Controller
{
    public function payment(): void
    {
        $this->requireAdmin();
        $this->render('admin/settings/payment', [
            'title' => 'Thanh toán & đặt cọc',
            'details' => (new SiteSetting())->paymentDetails(),
            'flash' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function savePayment(): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $details = [];
        foreach (['bank_name', 'bank_code', 'bank_account', 'bank_holder', 'bank_branch'] as $field) {
            $details[$field] = trim((string) ($_POST[$field] ?? ''));
            if (mb_strlen($details[$field]) > 120) {
                $_SESSION['_flash'] = 'Thông tin ngân hàng vượt quá độ dài cho phép.';
                $this->redirect('/admin/thanh-toan');
            }
        }
        if (
            ($details['bank_name'] !== '' || $details['bank_account'] !== '' || $details['bank_holder'] !== '')
            && (
                $details['bank_name'] === ''
                || !preg_match('/^[0-9]{6,30}$/', $details['bank_account'])
                || $details['bank_holder'] === ''
            )
        ) {
            $_SESSION['_flash'] = 'Để bật chuyển khoản, hãy nhập tên ngân hàng, số tài khoản 6-30 chữ số và tên chủ tài khoản.';
            $this->redirect('/admin/thanh-toan');
        }

        (new SiteSetting())->savePaymentDetails($details);
        $_SESSION['_flash'] = 'Đã lưu thông tin thanh toán cho các yêu cầu đặt cọc mới.';
        $this->redirect('/admin/thanh-toan');
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_string($message) ? $message : null;
    }
}
