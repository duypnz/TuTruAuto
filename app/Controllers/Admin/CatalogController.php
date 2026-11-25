<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\CatalogOption;

final class CatalogController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->render('admin/catalog/index', [
            'title' => 'Hãng xe & kiểu dáng',
            'options' => (new CatalogOption())->all(),
            'flash' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $type = (string) ($_POST['option_type'] ?? '');
        $name = trim((string) ($_POST['name'] ?? ''));
        if (
            !in_array($type, ['brand', 'body_type'], true)
            || $name === ''
            || mb_strlen($name) > 80
        ) {
            $_SESSION['_flash'] = 'Vui lòng nhập tên hãng hoặc kiểu dáng hợp lệ.';
            $this->redirect('/admin/danh-muc');
        }

        $added = (new CatalogOption())->add($type, $name);
        $_SESSION['_flash'] = $added
            ? 'Đã thêm lựa chọn danh mục.'
            : 'Lựa chọn này đã tồn tại.';
        $this->redirect('/admin/danh-muc');
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_string($message) ? $message : null;
    }
}
