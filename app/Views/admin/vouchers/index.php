<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/Helpers/view.php';

use App\Core\Csrf;

$title = $title ?? 'Quản lý mã giảm giá';
$vouchers = $vouchers ?? [];
$filters = $filters ?? ['keyword' => '', 'status' => ''];
$editing = $editing ?? null;
$form = $form ?? [];
$errors = $errors ?? [];
$brands = $brands ?? [];
$cars = $cars ?? [];
$flash = $flash ?? null;

$selected = static function (string $key, string $value) use ($form): string {
    return (string) ($form[$key] ?? '') === $value ? ' selected' : '';
};

ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading">
        <div><span class="eyebrow"><span></span> KHUYẾN MÃI</span><h1>Quản lý mã giảm giá</h1>
            <p>Thiết lập giảm giá theo phần trăm hoặc số tiền cố định, giới hạn lượt dùng, phạm vi hãng xe/mẫu xe và mã riêng cho từng khách.</p>
        </div>
        <span class="admin-badge"><?= count($vouchers) ?> mã</span>
    </div>

    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <?php if (isset($errors['_form'])): ?><div class="auth-alert" role="alert"><?= e($errors['_form']) ?></div><?php endif; ?>

    <div class="admin-panel order-search-panel">
        <div class="order-search-heading"><div><h2>Tìm mã giảm giá</h2><p>Tìm theo mã, tên hoặc email khách hàng được cấp riêng.</p></div></div>
        <form method="get" action="<?= e(url('/admin/voucher')) ?>" class="order-search-form">
            <label class="order-search-keyword">Từ khóa<input type="search" name="q" value="<?= e($filters['keyword']) ?>" placeholder="Ví dụ: TUTRU5"></label>
            <label>Trạng thái<select name="status">
                <option value="">Tất cả</option>
                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Đang áp dụng</option>
                <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Đang tắt</option>
                <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
            </select></label>
            <div class="order-search-actions"><button class="button button-dark" type="submit">Tìm mã</button><a href="<?= e(url('/admin/voucher')) ?>">Xóa lọc</a></div>
        </form>
    </div>

    <div class="admin-panel">
        <h2><?= $editing ? 'Sửa mã ' . e($editing['code']) : 'Thêm mã giảm giá' ?></h2>
        <form method="post" action="<?= e(url($editing ? '/admin/voucher/' . (int) $editing['id'] . '/cap-nhat' : '/admin/voucher')) ?>" class="auth-form">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Mã giảm giá<input name="code" maxlength="40" required value="<?= e($form['code'] ?? '') ?>" placeholder="TUTRU5"><small>Mã sẽ được chuyển thành chữ hoa.</small><?php if (isset($errors['code'])): ?><small style="color:#b34436"><?= e($errors['code']) ?></small><?php endif; ?></label>
            <label>Tên hiển thị<input name="name" maxlength="120" value="<?= e($form['name'] ?? '') ?>" placeholder="Ưu đãi tháng này"><?php if (isset($errors['name'])): ?><small style="color:#b34436"><?= e($errors['name']) ?></small><?php endif; ?></label>
            <label>Loại giảm giá<select name="discount_type" required>
                <option value="percent" <?= $selected('discount_type', 'percent') ?>>Phần trăm (%)</option>
                <option value="fixed" <?= $selected('discount_type', 'fixed') ?>>Số tiền cố định (₫)</option>
            </select><?php if (isset($errors['discount_type'])): ?><small style="color:#b34436"><?= e($errors['discount_type']) ?></small><?php endif; ?></label>
            <label>Giá trị giảm<input name="discount_value" type="number" min="1" step="1" required value="<?= e($form['discount_value'] ?? '') ?>"><small>VD: 5 là giảm 5%; 20000000 là giảm 20 triệu.</small><?php if (isset($errors['discount_value'])): ?><small style="color:#b34436"><?= e($errors['discount_value']) ?></small><?php endif; ?></label>
            <label>Trần giảm giá (₫)<input name="max_discount" type="number" min="0" step="1000000" value="<?= e($form['max_discount'] ?? '') ?>" placeholder="Để trống là không giới hạn"><?php if (isset($errors['max_discount'])): ?><small style="color:#b34436"><?= e($errors['max_discount']) ?></small><?php endif; ?></label>
            <label>Giá trị đơn tối thiểu (₫)<input name="minimum_order" type="number" min="0" step="1000000" required value="<?= e($form['minimum_order'] ?? '') ?>"><?php if (isset($errors['minimum_order'])): ?><small style="color:#b34436"><?= e($errors['minimum_order']) ?></small><?php endif; ?></label>
            <label>Phạm vi áp dụng<select name="scope_type" required>
                <option value="all" <?= $selected('scope_type', 'all') ?>>Tất cả xe</option>
                <option value="brand" <?= $selected('scope_type', 'brand') ?>>Theo hãng xe</option>
                <option value="car" <?= $selected('scope_type', 'car') ?>>Theo mẫu xe</option>
            </select><?php if (isset($errors['scope_type'])): ?><small style="color:#b34436"><?= e($errors['scope_type']) ?></small><?php endif; ?></label>
            <label>Giá trị phạm vi<input name="scope_value" maxlength="190" list="voucher-scope-options" value="<?= e($form['scope_value'] ?? '') ?>" placeholder="Toyota hoặc toyota-camry-hev-premium">
                <datalist id="voucher-scope-options">
                    <?php foreach ($brands as $brand): ?><option value="<?= e($brand) ?>"><?= e($brand) ?></option><?php endforeach; ?>
                    <?php foreach ($cars as $car): ?><option value="<?= e($car['slug']) ?>"><?= e($car['name']) ?></option><?php endforeach; ?>
                </datalist>
                <small>Chọn hãng xe hoặc dán slug mẫu xe.</small><?php if (isset($errors['scope_value'])): ?><small style="color:#b34436"><?= e($errors['scope_value']) ?></small><?php endif; ?></label>
            <label>Email khách hàng được cấp riêng<input name="user_email" type="email" maxlength="190" value="<?= e($form['user_email'] ?? '') ?>" placeholder="Để trống nếu dùng cho mọi tài khoản"><?php if (isset($errors['user_email'])): ?><small style="color:#b34436"><?= e($errors['user_email']) ?></small><?php endif; ?></label>
            <label>Từ ngày giờ<input name="starts_at" type="datetime-local" required value="<?= e($form['starts_at_form'] ?? $form['starts_at'] ?? '') ?>"></label>
            <label>Đến ngày giờ<input name="ends_at" type="datetime-local" required value="<?= e($form['ends_at_form'] ?? $form['ends_at'] ?? '') ?>"></label>
            <?php if (isset($errors['period'])): ?><small style="color:#b34436;grid-column:1/-1"><?= e($errors['period']) ?></small><?php endif; ?>
            <label>Tổng lượt dùng<input name="usage_limit" type="number" min="0" step="1" value="<?= e($form['usage_limit'] ?? '') ?>" placeholder="Để trống là không giới hạn"><?php if (isset($errors['usage_limit'])): ?><small style="color:#b34436"><?= e($errors['usage_limit']) ?></small><?php endif; ?></label>
            <label>Lượt dùng mỗi khách<input name="usage_per_user" type="number" min="0" step="1" value="<?= e($form['usage_per_user'] ?? '') ?>" placeholder="Để trống là không giới hạn"><?php if (isset($errors['usage_per_user'])): ?><small style="color:#b34436"><?= e($errors['usage_per_user']) ?></small><?php endif; ?></label>
            <label class="voucher-active-check"><input type="checkbox" name="is_active" value="1" <?= !empty($form['is_active']) ? 'checked' : '' ?>> Cho phép áp dụng mã</label>
            <button class="button button-dark auth-submit" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Thêm mã giảm giá' ?></button>
            <?php if ($editing): ?><a class="text-link" href="<?= e(url('/admin/voucher')) ?>">Hủy sửa</a><?php endif; ?>
        </form>
    </div>

    <div class="admin-panel">
        <h2>Danh sách mã</h2>
        <?php if ($vouchers): ?>
            <table class="admin-data-table">
                <thead><tr><th>Mã</th><th>Giảm giá</th><th>Phạm vi</th><th>Lượt dùng</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($vouchers as $voucher): ?>
                    <?php
                    $expired = strtotime((string) $voucher['ends_at']) < time();
                    $active = (int) $voucher['is_active'] === 1 && !$expired;
                    $scopeLabel = match ((string) $voucher['scope_type']) {
                        'brand' => 'Hãng: ' . (string) $voucher['scope_value'],
                        'car' => 'Xe: ' . (string) $voucher['scope_value'],
                        default => 'Tất cả xe',
                    };
                    $discountLabel = $voucher['discount_type'] === 'percent'
                        ? (int) $voucher['discount_value'] . '%'
                        : money($voucher['discount_value']);
                    if (!empty($voucher['max_discount'])) {
                        $discountLabel .= ' · tối đa ' . money($voucher['max_discount']);
                    }
                    ?>
                    <tr>
                        <td><strong><?= e($voucher['code']) ?></strong><br><small><?= e($voucher['name'] ?? '') ?></small></td>
                        <td><?= e($discountLabel) ?><br><small>Tối thiểu <?= money($voucher['minimum_order']) ?></small></td>
                        <td><?= e($scopeLabel) ?><?php if (!empty($voucher['user_email'])): ?><br><small>Riêng: <?= e($voucher['user_email']) ?></small><?php endif; ?></td>
                        <td><?= (int) $voucher['used_count'] ?><?= $voucher['usage_limit'] !== null ? ' / ' . (int) $voucher['usage_limit'] : '' ?><br><small><?= $voucher['usage_per_user'] !== null ? (int) $voucher['usage_per_user'] . ' lượt/khách' : 'Không giới hạn/khách' ?></small></td>
                        <td><small><?= e(date('d/m/Y H:i', strtotime((string) $voucher['starts_at']))) ?><br>→ <?= e(date('d/m/Y H:i', strtotime((string) $voucher['ends_at']))) ?></small></td>
                        <td><span class="order-status <?= $active ? 'status-deposit_paid' : 'status-cancelled' ?>"><?= $active ? 'Đang áp dụng' : ($expired ? 'Hết hạn' : 'Đang tắt') ?></span></td>
                        <td><a href="<?= e(url('/admin/voucher/' . (int) $voucher['id'] . '/sua')) ?>">Sửa</a>
                            <form method="post" action="<?= e(url('/admin/voucher/' . (int) $voucher['id'] . '/xoa')) ?>" style="display:inline">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <button type="submit" class="text-link">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Chưa có mã giảm giá nào.</p>
        <?php endif; ?>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
