<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'Giỏ hàng';
$cart = $cart ?? [];
$success = $success ?? null;
$error = $error ?? null;
$csrf = $csrf ?? \App\Core\Csrf::token();
$itemsTotal = 0;
$discountTotal = 0;
foreach ($cart as $item) {
    $itemsTotal += (int) $item['unit_price'] * (int) $item['quantity'];
    $discountTotal += (int) ($item['discount_total'] ?? 0);
}
$discountTotal = min($itemsTotal, $discountTotal);
ob_start();
?>
<section class="admin-page">
    <div class="container">
        <div class="admin-heading">
            <div><span class="eyebrow"><span></span> TU TRU AUTO</span><h1>Giỏ hàng của bạn</h1><p>Kiểm tra xe đã chọn, cập nhật số lượng và gửi yêu cầu mua xe hoặc đặt cọc.</p></div>
            <span class="admin-badge"><?= count($cart) ?> mẫu xe</span>
        </div>
        <?php if (is_string($success) && $success !== ''): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
        <?php if (is_string($error) && $error !== ''): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>

        <?php if ($cart): ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($cart as $key => $item): ?>
                        <article class="cart-item">
                            <a href="<?= e(url('/xe/' . $item['slug'])) ?>"><img src="<?= e(image_src($item['image_url'])) ?>" alt="<?= e($item['name']) ?>"></a>
                            <div class="cart-item-info">
                                <h2><a href="<?= e(url('/xe/' . $item['slug'])) ?>"><?= e($item['name']) ?></a></h2>
                                <?php if (!empty($item['options'])): ?><p><?= e(implode(' · ', (array) $item['options'])) ?></p><?php endif; ?>
                                <?php if (!empty($item['voucher_code'])): ?><small class="cart-voucher">Voucher <?= e($item['voucher_code']) ?> · giảm <?= money($item['discount_total'] ?? 0) ?></small><?php endif; ?>
                                <strong><?= money($item['unit_price']) ?> / xe</strong>
                            </div>
                            <div class="cart-item-controls">
                                <form method="post" action="<?= e(url('/gio-hang/cap-nhat')) ?>" class="cart-quantity-form">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="key" value="<?= e($key) ?>">
                                    <label>Số lượng<input type="number" name="quantity" min="1" max="5" value="<?= e($item['quantity']) ?>"></label>
                                    <button class="button button-dark" type="submit" name="action" value="update">Cập nhật</button>
                                </form>
                                <form method="post" action="<?= e(url('/gio-hang/cap-nhat')) ?>">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="key" value="<?= e($key) ?>">
                                    <button class="cart-remove" type="submit" name="action" value="remove">Xóa xe</button>
                                </form>
                            </div>
                            <div class="cart-line-total"><?= money((int) $item['unit_price'] * (int) $item['quantity']) ?></div>
                        </article>
                    <?php endforeach; ?>
                    <a class="text-link" href="<?= e(url('/xe')) ?>">← Tiếp tục chọn xe</a>
                </div>

                <aside class="cart-summary">
                    <h2>Thông tin đơn hàng</h2>
                    <div><span>Tạm tính</span><strong><?= money($itemsTotal) ?></strong></div>
                    <?php if ($discountTotal > 0): ?><div><span>Giảm giá voucher</span><strong>- <?= money($discountTotal) ?></strong></div><?php endif; ?>
                    <div class="cart-total"><span>Tổng dự kiến</span><strong><?= money($itemsTotal - $discountTotal) ?></strong></div>
                    <p>Đơn hàng được ghi nhận để nhân viên xác nhận. Trạng thái đặt cọc/mua xe sẽ được cập nhật trong tài khoản sau khi showroom xác nhận.</p>
                    <form method="post" action="<?= e(url('/gio-hang/dat-hang')) ?>" class="cart-checkout-form">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <label>Họ tên<input name="customer_name" maxlength="120" value="<?= e($_SESSION['user']['name'] ?? '') ?>" required></label>
                        <label>Số điện thoại<input name="phone" type="tel" maxlength="30" required></label>
                        <label>Ghi chú<textarea name="message" rows="3" maxlength="1000" placeholder="Thời gian liên hệ, phiên bản hoặc yêu cầu khác"></textarea></label>
                        <label>Voucher đã áp dụng
                            <select name="voucher_code">
                                <option value="">Không dùng voucher</option>
                                <?php foreach (array_unique(array_filter(array_column($cart, 'voucher_code'))) as $voucher): ?>
                                    <option value="<?= e($voucher) ?>"><?= e($voucher) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="cart-submit-actions">
                            <button class="button button-dark" type="submit" name="order_type" value="purchase">Gửi yêu cầu mua xe</button>
                            <button class="button button-outline" type="submit" name="order_type" value="deposit">Yêu cầu đặt cọc</button>
                        </div>
                    </form>
                </aside>
            </div>
        <?php else: ?>
            <div class="empty-state"><span>◇</span><h2>Giỏ hàng đang trống</h2><p>Thêm mẫu xe bạn quan tâm, sau đó gửi yêu cầu mua xe hoặc đặt cọc.</p><a class="button button-dark" href="<?= e(url('/xe')) ?>">Khám phá xe</a></div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
