<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = 'Không đủ quyền truy cập';
ob_start();
?>
<section class="page-empty container"><span>403</span><h1>Khu vực dành riêng cho quản trị viên</h1><p>Tài khoản của bạn không có quyền truy cập trang này.</p><a class="button button-dark" href="<?= e(url('/')) ?>">Về trang chủ <span>→</span></a></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
