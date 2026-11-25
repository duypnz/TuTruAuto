<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;

$title = $title ?? 'Thanh toán & đặt cọc';
$details = $details ?? [];
$flash = $flash ?? null;
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> CẤU HÌNH THANH TOÁN</span><h1>Thông tin nhận đặt cọc</h1><p>Thông tin ngân hàng được chụp lại trên từng đơn mới để khách không chuyển nhầm khi cấu hình thay đổi.</p></div><span class="admin-badge">ĐẶT CỌC 10%</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <div class="admin-panel">
        <form method="post" action="<?= e(url('/admin/thanh-toan')) ?>" class="admin-payment-form">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <label>Tên ngân hàng<input name="bank_name" maxlength="120" value="<?= e($details['bank_name'] ?? '') ?>" placeholder="Ví dụ: Ngân hàng ..."></label>
            <label>Mã ngân hàng (không bắt buộc)<input name="bank_code" maxlength="120" value="<?= e($details['bank_code'] ?? '') ?>"></label>
            <label>Số tài khoản<input name="bank_account" inputmode="numeric" maxlength="30" value="<?= e($details['bank_account'] ?? '') ?>" placeholder="6-30 chữ số"></label>
            <label>Chủ tài khoản<input name="bank_holder" maxlength="120" value="<?= e($details['bank_holder'] ?? '') ?>"></label>
            <label>Chi nhánh (không bắt buộc)<input name="bank_branch" maxlength="120" value="<?= e($details['bank_branch'] ?? '') ?>"></label>
            <div class="admin-payment-note"><strong>Quy trình thanh toán</strong><p>Tiền cọc được tự động tính bằng 10% tổng giá sau ưu đãi. Khách cũng có thể nộp tiền mặt tại showroom. Nhân viên xác nhận giao dịch thủ công; website không tự thu tiền.</p></div>
            <button class="button button-dark" type="submit">Lưu thông tin chuyển khoản</button>
        </form>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
