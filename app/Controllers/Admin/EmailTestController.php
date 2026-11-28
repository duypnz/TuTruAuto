<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Environment;
use App\Services\EmailService;
use Throwable;

final class EmailTestController extends Controller
{
    public function index(): void
    {
        if (!$this->requireDevelopment()) {
            return;
        }
        $this->requireAdmin();

        $message = $_SESSION['_email_test_message'] ?? null;
        $success = $_SESSION['_email_test_success'] ?? false;
        unset($_SESSION['_email_test_message'], $_SESSION['_email_test_success']);

        $this->render('admin/email-test', [
            'title' => 'Kiểm thử email',
            'csrf' => Csrf::token(),
            'message' => is_string($message) ? $message : null,
            'success' => $success === true,
        ]);
    }

    public function send(): void
    {
        if (!$this->requireDevelopment()) {
            return;
        }
        $this->requireAdmin();

        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $recipient = trim((string) ($_POST['recipient'] ?? ''));
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_email_test_message'] = 'Địa chỉ email nhận thử không hợp lệ.';
            $_SESSION['_email_test_success'] = false;
            $this->redirect('/admin/email-test');
        }

        try {
            (new EmailService())->sendTestEmail($recipient);
            $_SESSION['_email_test_message'] = 'Gửi email kiểm thử thành công.';
            $_SESSION['_email_test_success'] = true;
        } catch (Throwable $error) {
            error_log('Email SMTP test failed: ' . $error->getMessage());
            $_SESSION['_email_test_message'] = 'Gửi email kiểm thử thất bại. Hãy kiểm tra cấu hình SMTP và log lỗi PHP.';
            $_SESSION['_email_test_success'] = false;
        }

        $this->redirect('/admin/email-test');
    }

    private function requireDevelopment(): bool
    {
        if (Environment::get('APP_ENV') === 'development') {
            return true;
        }

        http_response_code(404);
        $this->render('errors/404');
        return false;
    }
}
