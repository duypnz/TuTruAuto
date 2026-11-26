<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = $title ?? 'Đơn đặt cọc';
$orders = $orders ?? [];
$success = $success ?? null;
$error = $error ?? null;
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'deposit_pending' => 'Chờ xác nhận đặt cọc',
    'deposit_paid' => 'Đã đặt cọc',
    'purchased' => 'Đã mua xe',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn cọc',
];
$typeLabels = ['purchase' => 'Đơn mua lịch sử', 'deposit' => 'Đặt cọc'];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> TÀI KHOẢN CỦA BẠN</span><h1>Đơn đặt cọc của tôi</h1><p>Theo dõi trạng thái, số tiền cọc và lịch nhận xe đã hẹn.</p></div><a class="button button-dark" href="<?= e(url('/xe')) ?>">Chọn xe</a></div>
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
                    <div class="customer-deposit-details">
                        <div><span>Giá xe dự kiến</span><strong><?= money($order['total']) ?></strong></div>
                        <div><span>Tiền đặt cọc</span><strong><?= $order['deposit_amount'] !== null ? money($order['deposit_amount']) : 'Chưa được xác nhận' ?></strong></div>
                        <div><span>Thời gian nhận xe</span><strong><?= !empty($order['delivery_at']) ? e(date('d/m/Y H:i', strtotime($order['delivery_at']))) : 'Showroom sẽ cập nhật sau' ?></strong></div>
                    </div>
                    <?php if ((int) $order['discount_total'] > 0): ?><p class="order-message">Đã áp dụng ưu đãi: <?= money($order['discount_total']) ?></p><?php endif; ?>
                    <?php if (!empty($order['refund_amount'])): ?><p class="order-message">Đã hoàn cọc: <?= money($order['refund_amount']) ?></p><?php endif; ?>
                    <?php if (!empty($order['message'])): ?><p class="order-message"><?= e($order['message']) ?></p><?php endif; ?>
                    <a class="button button-outline order-detail-link" href="<?= e(url('/tai-khoan/don-hang/' . (int) $order['id'])) ?>">Xem chi tiết đơn ↗</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><span>◇</span><h2>Bạn chưa có đơn đặt cọc</h2><p>Các yêu cầu đặt cọc đã gửi tới showroom sẽ xuất hiện tại đây.</p><a class="button button-dark" href="<?= e(url('/xe')) ?>">Xem danh sách xe</a></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
