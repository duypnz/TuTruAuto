<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;
$title = $title ?? 'Quản lý đơn đặt cọc';
$orders = $orders ?? [];
$flash = $flash ?? null;
$error = $error ?? null;
$filters = $filters ?? ['keyword' => '', 'status' => '', 'from' => '', 'to' => ''];
$histories = $histories ?? [];
$statusLabels = [
    'pending' => 'Chờ xác nhận mua xe',
    'deposit_pending' => 'Chờ xác nhận đặt cọc',
    'deposit_paid' => 'Đã đặt cọc',
    'purchased' => 'Đã mua xe',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn cọc',
];
$actionLabels = [
    'status_update' => 'Cập nhật trạng thái',
    'payment_verify' => 'Đối soát thanh toán',
    'cancel' => 'Hủy đơn',
    'refund' => 'Hoàn cọc',
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
                <?php
                $orderId = (int) $order['id'];
                $terminal = in_array($order['status'], ['cancelled', 'refunded'], true);
                $orderHistory = $histories[$orderId] ?? [];
                ?>
                <article class="customer-order admin-order">
                    <header><div><strong>Đơn #<?= e($orderId) ?></strong><span><?= e($typeLabels[$order['order_type']] ?? $order['order_type']) ?></span></div>
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
                        <div><span>Đối soát tiền cọc</span><strong><?= !empty($order['payment_verified_at']) ? 'Đã xác nhận · ' . e(date('d/m/Y H:i', strtotime($order['payment_verified_at']))) : 'Chưa xác nhận' ?></strong></div>
                        <div><span>Tham chiếu giao dịch</span><strong><?= !empty($order['payment_reference']) ? e($order['payment_reference']) : '—' ?></strong></div>
                        <div><span>Ghi chú đối soát</span><strong><?= !empty($order['payment_note']) ? e($order['payment_note']) : '—' ?></strong></div>
                        <?php if (!empty($order['cancel_reason'])): ?><div><span>Lý do hủy</span><strong><?= e($order['cancel_reason']) ?></strong></div><?php endif; ?>
                        <?php if (!empty($order['refund_amount'])): ?><div><span>Đã hoàn cọc</span><strong><?= money($order['refund_amount']) ?> · <?= $order['refund_method'] === 'bank' ? 'Chuyển khoản' : 'Tiền mặt' ?></strong></div><?php endif; ?>
                    </div>
                    <?php if ($orderHistory): ?>
                        <div class="order-history">
                            <strong>Lịch sử xử lý</strong>
                            <?php foreach ($orderHistory as $entry): ?>
                                <div class="order-history-item">
                                    <span><?= e(date('d/m/Y H:i', strtotime($entry['created_at']))) ?></span>
                                    <span class="order-status status-<?= e($entry['to_status']) ?>"><?= e($statusLabels[$entry['to_status']] ?? $entry['to_status']) ?></span>
                                    <p><?= e($actionLabels[$entry['action']] ?? $entry['action']) ?><?= !empty($entry['actor_name']) ? ' · ' . e($entry['actor_name']) : '' ?><?= !empty($entry['note']) ? ' · ' . e($entry['note']) : '' ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!$terminal): ?>
                        <form method="post" action="<?= e(url('/admin/don-hang/' . $orderId . '/cap-nhat')) ?>" class="order-status-form">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <label class="order-status-field">Cập nhật trạng thái
                                <select name="status"><?php foreach (['pending', 'deposit_pending', 'deposit_paid', 'purchased'] as $key): ?><option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($statusLabels[$key]) ?></option><?php endforeach; ?></select>
                            </label>
                            <label>Ngày giờ nhận xe
                                <input type="datetime-local" name="delivery_at" value="<?= !empty($order['delivery_at']) ? e(date('Y-m-d\TH:i', strtotime($order['delivery_at']))) : '' ?>">
                            </label>
                            <button class="button button-dark" type="submit">Lưu cập nhật</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($order['status'], ['pending', 'deposit_pending', 'deposit_paid'], true)): ?>
                        <form method="post" action="<?= e(url('/admin/don-hang/' . $orderId . '/doi-soat')) ?>" class="order-status-form order-action-form">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <label>Tham chiếu giao dịch<input name="payment_reference" maxlength="120" value="<?= e($order['payment_reference'] ?? '') ?>" placeholder="Mã giao dịch/nội dung chuyển khoản"></label>
                            <label class="order-action-note">Ghi chú đối soát<textarea name="payment_note" maxlength="500" rows="2" placeholder="Ghi chú luồng tiền, người nộp..."><?= e($order['payment_note'] ?? '') ?></textarea></label>
                            <label class="voucher-active-check"><input type="checkbox" name="verified" value="1" <?= $order['status'] === 'deposit_paid' ? 'checked' : '' ?>> Đã nhận tiền cọc</label>
                            <button class="button button-outline" type="submit">Lưu đối soát</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($order['status'], ['pending', 'deposit_pending', 'deposit_paid'], true)): ?>
                        <form method="post" action="<?= e(url('/admin/don-hang/' . $orderId . '/huy')) ?>" class="order-status-form order-action-form">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <label class="order-action-note">Lý do hủy đơn<input name="cancel_reason" maxlength="500" required placeholder="Khách hủy, không còn xe..."></label>
                            <button class="button button-outline" type="submit">Hủy đơn</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($order['status'], ['deposit_paid', 'cancelled'], true)): ?>
                        <form method="post" action="<?= e(url('/admin/don-hang/' . $orderId . '/hoan-coc')) ?>" class="order-status-form order-action-form">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <label>Số tiền hoàn<input name="refund_amount" type="number" min="1" step="1000000" required value="<?= max(0, (int) ($order['deposit_amount'] ?? 0) - (int) ($order['refund_amount'] ?? 0)) ?>"></label>
                            <label>Phương thức<select name="refund_method"><option value="bank" <?= ($order['refund_method'] ?? 'bank') === 'bank' ? 'selected' : '' ?>>Chuyển khoản</option><option value="cash" <?= ($order['refund_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Tiền mặt</option></select></label>
                            <label class="order-action-note">Ghi chú hoàn tiền<input name="refund_note" maxlength="500" placeholder="Số tài khoản, ngày chuyển..."></label>
                            <button class="button button-outline" type="submit">Ghi nhận hoàn cọc</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><span>◇</span><h2>Không tìm thấy đơn phù hợp</h2><p>Thử thay đổi từ khóa, trạng thái hoặc khoảng thời gian tìm kiếm.</p></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
