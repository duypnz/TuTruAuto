<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;
$title = $title ?? 'Quản lý đơn đặt cọc';
$orders = $orders ?? [];
$flash = $flash ?? null;
$error = $error ?? null;
$filters = $filters ?? ['keyword' => '', 'status' => '', 'from' => '', 'to' => ''];
$statusLabels = [
    'pending' => 'Chờ xác nhận mua xe',
    'deposit_pending' => 'Chờ xác nhận đặt cọc',
    'deposit_paid' => 'Đã đặt cọc',
    'purchased' => 'Đã mua xe',
    'cancelled' => 'Đã hủy',
];
$typeLabels = ['purchase' => 'Đơn mua lịch sử', 'deposit' => 'Đặt cọc'];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> KHU VỰC QUẢN TRỊ</span><h1>Quản lý đơn đặt cọc</h1><p>Tìm kiếm yêu cầu, xác nhận tiền cọc và hẹn thời gian nhận xe.</p></div><span class="admin-badge"><?= count($orders) ?> kết quả</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <div class="admin-panel order-search-panel">
        <div class="order-search-heading"><div><h2>Tìm kiếm đơn hàng</h2><p>Tên, số điện thoại, email, mã đơn hoặc tên xe.</p></div><a class="button button-outline" href="<?= e(url('/admin')) ?>">← Dashboard</a></div>
        <form method="get" action="<?= e(url('/admin/don-hang')) ?>" class="order-search-form">
            <label class="order-search-keyword">Từ khóa<input type="search" name="q" value="<?= e($filters['keyword']) ?>" placeholder="Ví dụ: Nguyễn Văn A, 090..., mã đơn #12"></label>
            <label>Trạng thái<select name="status"><option value="">Tất cả trạng thái</option><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
            <label>Từ ngày<input type="date" name="from" value="<?= e($filters['from']) ?>"></label>
            <label>Đến ngày<input type="date" name="to" value="<?= e($filters['to']) ?>"></label>
            <div class="order-search-actions"><button class="button button-dark" type="submit">Tìm đơn</button><a href="<?= e(url('/admin/don-hang')) ?>">Xóa lọc</a></div>
        </form>
    </div>
    <?php if ($orders): ?>
        <div class="order-list">
            <?php foreach ($orders as $order): ?>
                <article class="customer-order admin-order">
                    <header><div><strong>Đơn #<?= e($order['id']) ?></strong><span><?= e($typeLabels[$order['order_type']] ?? $order['order_type']) ?></span></div>
                        <span class="order-status status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
                    </header>
                    <div class="admin-order-customer"><strong><?= e($order['customer_name']) ?></strong> · <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $order['phone'])) ?>"><?= e($order['phone']) ?></a>
                        <?php if ($order['email']): ?><span> · <?= e($order['email']) ?></span><?php else: ?><span> · Khách chưa đăng nhập</span><?php endif; ?>
                        <small><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></small>
                    </div>
                    <div class="admin-order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-car"><img src="<?= e(image_src($item['image_url'])) ?>" alt=""><div><strong><?= e($item['name']) ?></strong>
                                <?php if ($item['options']): ?><small><?= e(implode(' · ', $item['options'])) ?></small><?php endif; ?>
                                <small><?= e($item['quantity']) ?> xe · <?= money($item['unit_price']) ?>/xe</small></div></div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($order['message'])): ?><p class="order-message"><?= e($order['message']) ?></p><?php endif; ?>
                    <footer><span>Tổng <strong><?= money($order['total']) ?></strong></span><?php if ((int) $order['discount_total'] > 0): ?><small>Giảm <?= money($order['discount_total']) ?><?= $order['voucher_code'] ? ' · ' . e($order['voucher_code']) : '' ?></small><?php endif; ?></footer>
                    <div class="admin-order-details">
                        <div><span>Tổng giá trị xe</span><strong><?= money($order['total']) ?></strong></div>
                        <div><span>Tiền cọc (10%)</span><strong><?= $order['deposit_amount'] !== null ? money($order['deposit_amount']) : 'Chưa cập nhật' ?></strong></div>
                        <div><span>Lịch nhận xe</span><strong><?= !empty($order['delivery_at']) ? e(date('d/m/Y H:i', strtotime($order['delivery_at']))) : 'Chưa hẹn' ?></strong></div>
                    </div>
                    <form method="post" action="<?= e(url('/admin/don-hang/' . (int) $order['id'] . '/cap-nhat')) ?>" class="order-status-form">
                        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                        <label class="order-status-field">Cập nhật trạng thái
                            <select name="status"><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
                        </label>
                        <label>Ngày giờ nhận xe
                            <input type="datetime-local" name="delivery_at" value="<?= !empty($order['delivery_at']) ? e(date('Y-m-d\TH:i', strtotime($order['delivery_at']))) : '' ?>">
                        </label>
                        <button class="button button-dark" type="submit">Lưu cập nhật</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><span>◇</span><h2>Không tìm thấy đơn phù hợp</h2><p>Thử thay đổi từ khóa, trạng thái hoặc khoảng thời gian tìm kiếm.</p></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
