<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'Chi tiết đơn đặt cọc';
$order = $order ?? [];
$success = $success ?? null;
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'deposit_pending' => 'Chờ xác nhận đặt cọc',
    'deposit_paid' => 'Đã đặt cọc',
    'purchased' => 'Đã hoàn tất',
    'cancelled' => 'Đã hủy',
];
$payment = is_array($order['payment_info'] ?? null) ? $order['payment_info'] : [];
$isPreorder = ($payment['inventory_status'] ?? '') === 'preorder';
$isInventoryUnverified = ($payment['inventory_status'] ?? '') === 'unverified';
$hasBankDetails = ($payment['bank_name'] ?? '') !== ''
    && ($payment['bank_account'] ?? '') !== ''
    && ($payment['bank_holder'] ?? '') !== '';
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading">
        <div><span class="eyebrow"><span></span> TÀI KHOẢN CỦA BẠN</span><h1>Chi tiết đơn #<?= e($order['id']) ?></h1><p>Thông tin xe, tiền đặt cọc, hướng dẫn thanh toán và lịch nhận xe.</p></div>
        <a class="button button-outline" href="<?= e(url('/tai-khoan/don-hang')) ?>">← Danh sách đơn</a>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
    <article class="customer-order">
        <header><div><strong>Trạng thái đơn</strong><span>Ngày tạo <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></span></div>
            <span class="order-status status-<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
        </header>
        <?php foreach ($order['items'] as $item): ?>
            <div class="order-car"><img src="<?= e(image_src($item['image_url'])) ?>" alt=""><div><strong><?= e($item['name']) ?></strong>
                <?php if ($item['options']): ?><small><?= e(implode(' · ', $item['options'])) ?></small><?php endif; ?>
                <small><?= e($item['quantity']) ?> xe · <?= money($item['unit_price']) ?>/xe</small>
            </div></div>
        <?php endforeach; ?>
        <?php if ($isPreorder): ?>
            <div class="preorder-notice" role="status">
                <strong>Đặt cọc chờ xe về</strong>
                <span>Thời gian dự kiến khoảng <?= (int) ($payment['lead_time_days'] ?? 21) ?> ngày
                    <?php if (!empty($payment['estimated_delivery_date'])): ?> (dự kiến từ <?= e(date('d/m/Y', strtotime($payment['estimated_delivery_date']))) ?>).<?php else: ?>; showroom sẽ xác nhận ngày nhận xe cụ thể.<?php endif; ?>
                </span>
            </div>
        <?php elseif ($isInventoryUnverified): ?>
            <div class="preorder-notice" role="status">
                <strong>Đang xác nhận tồn kho</strong>
                <span>Showroom sẽ kiểm tra số xe thực tế và liên hệ bạn trước khi xác nhận nhận tiền cọc.</span>
            </div>
        <?php endif; ?>
        <div class="customer-deposit-details">
            <div><span>Giá xe sau ưu đãi</span><strong><?= money($order['total']) ?></strong></div>
            <div><span>Tiền đặt cọc (10%)</span><strong><?= $order['deposit_amount'] !== null ? money($order['deposit_amount']) : money((int) round((int) $order['total'] * 0.1)) ?></strong></div>
            <div><span>Thời gian nhận xe</span><strong><?= !empty($order['delivery_at']) ? e(date('d/m/Y H:i', strtotime($order['delivery_at']))) : 'Showroom sẽ xác nhận sau' ?></strong></div>
        </div>
        <section class="payment-instructions">
            <h2>Phương thức thanh toán tiền cọc</h2>
            <?php if (($payment['method'] ?? 'bank') === 'bank'): ?>
            <div class="payment-method">
                <span class="payment-method-icon">↗</span>
                <div><h3>Chuyển khoản ngân hàng</h3>
                    <?php if ($hasBankDetails): ?>
                        <div class="fake-payment-qr" aria-label="Mã QR chuyển tiền giả lập">
                            <span class="fake-qr-mark">TTA</span>
                            <strong><?= money($order['deposit_amount'] ?? round((int) $order['total'] * 0.1)) ?></strong>
                            <small>Mã QR minh họa — chưa kích hoạt thanh toán</small>
                        </div>
                        <dl>
                            <div><dt>Ngân hàng</dt><dd><?= e($payment['bank_name']) ?><?= !empty($payment['bank_code']) ? ' (' . e($payment['bank_code']) . ')' : '' ?></dd></div>
                            <div><dt>Số tài khoản</dt><dd><?= e($payment['bank_account']) ?></dd></div>
                            <div><dt>Chủ tài khoản</dt><dd><?= e($payment['bank_holder']) ?></dd></div>
                            <?php if (!empty($payment['bank_branch'])): ?><div><dt>Chi nhánh</dt><dd><?= e($payment['bank_branch']) ?></dd></div><?php endif; ?>
                            <div><dt>Số tiền</dt><dd><?= money($order['deposit_amount'] ?? round((int) $order['total'] * 0.1)) ?></dd></div>
                            <div><dt>Nội dung</dt><dd>TTA đặt cọc đơn #<?= e($order['id']) ?></dd></div>
                        </dl>
                        <p>Vui lòng ghi đúng nội dung chuyển khoản. Showroom sẽ xác nhận sau khi đối soát giao dịch.</p>
                    <?php else: ?>
                        <p>Thông tin tài khoản nhận chuyển khoản chưa được cấu hình. Vui lòng liên hệ showroom trước khi thanh toán.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="payment-method">
                <span class="payment-method-icon">₫</span>
                <div><h3>Nộp tiền mặt tại showroom</h3><p>Vui lòng đến showroom để nộp tiền mặt và nhận phiếu thu. Chỉ nhân viên showroom mới xác nhận khoản đặt cọc.</p><strong>Showroom: Trịnh Văn Bô, Nam Từ Liêm, Hà Nội</strong></div>
            </div>
            <?php endif; ?>
            <p class="payment-disclaimer">Đây là yêu cầu đặt cọc, không phải xác nhận thanh toán tự động. Mã QR trên trang này là minh họa giả lập.</p>
        </section>
    </article>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
