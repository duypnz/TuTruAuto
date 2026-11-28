<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;

$title = $title ?? 'Kiểm thử email';
$message = $message ?? null;
$success = $success ?? false;
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading">
        <div><span class="eyebrow"><span></span> MÔI TRƯỜNG PHÁT TRIỂN</span><h1>Kiểm thử Gmail SMTP</h1><p>Gửi một email thử để xác nhận cấu hình SMTP đang hoạt động.</p></div>
        <span class="admin-badge">DEV ONLY</span>
    </div>
    <?php if (is_string($message) && $message !== ''): ?>
        <div class="<?= $success ? 'profile-success' : 'auth-alert' ?>" role="<?= $success ? 'status' : 'alert' ?>"><?= e($message) ?></div>
    <?php endif; ?>
    <div class="admin-panel">
        <form method="post" action="<?= e(url('/admin/email-test')) ?>" class="admin-payment-form">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <label>Email nhận thử<input type="email" name="recipient" maxlength="190" required autocomplete="email" placeholder="ban@example.com"></label>
            <p>Email sẽ có tiêu đề <strong>Test Email - TuTruAuto</strong> và nội dung kiểm thử cố định.</p>
            <button class="button button-dark" type="submit">Gửi email thử</button>
        </form>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
