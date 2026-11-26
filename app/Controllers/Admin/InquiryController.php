<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\CustomerInquiry;

final class InquiryController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('admin/inquiries/index', [
            'title' => 'Liên hệ khách hàng',
            'inquiries' => (new CustomerInquiry())->searchForAdmin($keyword, $status),
            'keyword' => $keyword,
            'status' => $status,
            'flash' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function updateStatus(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $status = (string) ($_POST['inquiry_status'] ?? '');
        if (!in_array($status, ['new', 'contacted', 'closed'], true)) {
            $_SESSION['_flash'] = 'Trạng thái liên hệ không hợp lệ.';
            $this->redirect('/admin/lien-he');
        }
        $updated = (new CustomerInquiry())->updateStatus((int) ($params['id'] ?? 0), $status);
        $_SESSION['_flash'] = $updated ? 'Đã cập nhật trạng thái liên hệ.' : 'Không tìm thấy liên hệ cần cập nhật.';
        $this->redirect('/admin/lien-he');
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_string($message) ? $message : null;
    }
}
