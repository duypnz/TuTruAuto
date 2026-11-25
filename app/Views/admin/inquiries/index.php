<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;

$title = $title ?? 'Liên hệ khách hàng';
$inquiries = $inquiries ?? [];
$keyword = $keyword ?? '';
$status = $status ?? '';
$flash = $flash ?? null;
$statusLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'closed' => 'Đã xử lý'];
$typeLabels = ['contact' => 'Liên hệ trang chủ', 'test_drive' => 'Lái thử', 'quote' => 'Báo giá', 'advice' => 'Tư vấn', 'deposit' => 'Đặt cọc', 'buy' => 'Mua xe'];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> CHĂM SÓC KHÁCH HÀNG</span><h1>Liên hệ khách hàng</h1><p>Quản lý yêu cầu từ trang chủ, trang chi tiết xe và lịch đăng ký lái thử.</p></div><span class="admin-badge"><?= count($inquiries) ?> yêu cầu</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <form method="get" action="<?= e(url('/admin/lien-he')) ?>" class="inquiry-filter">
        <label>Tìm khách, số điện thoại, email hoặc xe<input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Nhập từ khóa"></label>
        <label>Trạng thái<select name="status"><option value="">Tất cả</option><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
        <button class="button button-dark" type="submit">Lọc liên hệ</button><a href="<?= e(url('/admin/lien-he')) ?>">Xóa lọc</a>
    </form>
    <?php if ($inquiries): ?><div class="inquiry-list">
        <?php foreach ($inquiries as $inquiry): ?>
            <article class="admin-panel inquiry-card">
                <header><div><h2><?= e($inquiry['customer_name']) ?></h2><span><?= e($typeLabels[$inquiry['inquiry_type']] ?? $inquiry['inquiry_type']) ?><?= !empty($inquiry['car_name']) ? ' · ' . e($inquiry['car_name']) : '' ?></span></div><span class="order-status"><?= e($statusLabels[$inquiry['inquiry_status']] ?? $inquiry['inquiry_status']) ?></span></header>
                <p class="inquiry-contact"><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $inquiry['phone'])) ?>"><?= e($inquiry['phone']) ?></a><?= !empty($inquiry['email']) ? ' · ' . e($inquiry['email']) : '' ?><small><?= e(date('d/m/Y H:i', strtotime($inquiry['created_at']))) ?></small></p>
                <?php if (!empty($inquiry['message'])): ?><p class="inquiry-message"><?= nl2br(e($inquiry['message'])) ?></p><?php endif; ?>
                <form method="post" action="<?= e(url('/admin/lien-he/' . (int) $inquiry['id'] . '/cap-nhat')) ?>" class="inquiry-status-form">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                    <label>Trạng thái<select name="inquiry_status"><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $inquiry['inquiry_status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
                    <button class="button button-outline" type="submit">Cập nhật</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div><?php else: ?><div class="empty-state"><span>✉</span><h2>Chưa có yêu cầu liên hệ</h2><p>Yêu cầu của khách hàng sẽ xuất hiện tại đây.</p></div><?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
