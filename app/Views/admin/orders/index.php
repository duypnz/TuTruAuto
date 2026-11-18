<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;
$title = $title ?? 'Đơn mua xe';
$orders = $orders ?? [];
$flash = $flash ?? null;
$statusLabels = [
    'pending' => 'Chờ xác nhận mua xe',
    'deposit_pending' => 'Chờ xác nhận đặt cọc',
    'deposit_paid' => 'Đã đặt cọc',
    'purchased' => 'Đã mua xe',
    'cancelled' => 'Đã hủy',
];
$typeLabels = ['purchase' => 'Mua xe', 'deposit' => 'Đặt cọc'];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> KHU VỰC QUẢN TRỊ</span><h1>Đơn mua xe & đặt cọc</h1><p>Theo dõi khách hàng, xe đã chọn và cập nhật trạng thái xác nhận.</p></div><span class="admin-badge"><?= count($orders) ?> đơn</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <div class="admin-panel" style="margin-bottom:20px"><a class="button" href="<?= e(url('/admin')) ?>">← Dashboard</a></div>
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
                    <form method="post" action="<?= e(url('/admin/don-hang/' . (int) $order['id'] . '/trang-thai')) ?>" class="order-status-form">
                        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                        <label>Cập nhật trạng thái
                            <select name="status"><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
                        </label>
                        <button class="button button-dark" type="submit">Lưu trạng thái</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><span>◇</span><h2>Chưa có đơn mua xe hoặc đặt cọc</h2><p>Khi khách hàng gửi yêu cầu, đơn sẽ được hiển thị tại đây.</p></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
