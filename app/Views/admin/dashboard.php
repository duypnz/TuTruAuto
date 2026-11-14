<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> KHU VỰC QUẢN TRỊ</span><h1>Xin chào, <?= e($admin['name']) ?></h1><p>Đang đăng nhập với tài khoản quản trị <strong><?= e($admin['email']) ?></strong>.</p></div><span class="admin-badge">ADMIN</span></div>
    <div class="admin-stats"><article><span>Tổng tài khoản</span><strong><?= e($userCount) ?></strong><small>Khách hàng và quản trị viên</small></article><article><span>Xe trong hệ thống</span><strong><?= e($carCount) ?></strong><small>Toàn bộ tin xe đã lưu</small></article></div>
    <div class="admin-panel"><h2>Quản lý</h2><p><a class="button button-dark" href="<?= e(url('/admin/xe')) ?>">Quản lý xe</a></p></div>
<div class="admin-panel"><h2>Tài khoản quản trị</h2><p>Quyền quản trị được cấp riêng trong cơ sở dữ liệu. Người dùng đăng ký mới luôn chỉ có quyền khách hàng.</p><a class="button button-dark" href="<?= e(url('/')) ?>">Về trang chủ <span>↗</span></a></div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
