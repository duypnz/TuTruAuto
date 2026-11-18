<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = $title ?? 'Đơn mua xe';
$orders = $orders ?? [];
$success = $success ?? null;
$error = $error ?? null;
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
    <div class="admin-heading"><div><span class="eyebrow"><span></span> TÀI KHOẢN CỦA BẠN</span><h1>Đơn mua xe & đặt cọc</h1><p>Theo dõi trạng thái các yêu cầu đã gửi đến showroom.</p></div><a class="button button-dark" href="<?= e(url('/xe')) ?>">Chọn thêm xe</a></div>
    <?php if (is_string($success) && $success !== ''): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($orders): ?>
        <div class="order-list">
            <?php foreach ($orders as $order): ?>
                <article class="customer-order">
                    <header><div><strong>Đơn #<?= e($order['id']) ?></strong><span><?= e($typeLabels[$order['order_type']] ?? $order['order_type']) ?></span></div>
                        <span class="order-status status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
                    </header>
                    <p class="order-date">Ngày tạo: <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-car"><img src="<?= e(image_src($item['image_url'])) ?>" alt=""><div><strong><?= e($item['name']) ?></strong>
                            <?php if ($item['options']): ?><small><?= e(implode(' · ', $item['options'])) ?></small><?php endif; ?>
                            <small><?= e($item['quantity']) ?> xe · <?= money($item['unit_price']) ?>/xe</small></div>
                        </div>
                    <?php endforeach; ?>
                    <footer><span>Tổng dự kiến <strong><?= money($order['total']) ?></strong></span><?php if ((int) $order['discount_total'] > 0): ?><small>Đã giảm <?= money($order['discount_total']) ?></small><?php endif; ?></footer>
                    <?php if (!empty($order['message'])): ?><p class="order-message"><?= e($order['message']) ?></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><span>◇</span><h2>Bạn chưa có đơn hàng</h2><p>Đơn mua xe và yêu cầu đặt cọc sẽ xuất hiện tại đây.</p><a class="button button-dark" href="<?= e(url('/xe')) ?>">Xem danh sách xe</a></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
