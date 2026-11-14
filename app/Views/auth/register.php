<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = 'Tạo tài khoản';
ob_start();
?>
<section class="auth-section"><div class="auth-card"><span class="eyebrow"><span></span> BẮT ĐẦU HÀNH TRÌNH</span><h1>Tạo tài khoản</h1><p class="auth-intro">Lưu lại những chiếc xe bạn yêu thích và nhận hỗ trợ nhanh hơn.</p>
    <?php if ($error): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/dang-ky')) ?>" class="auth-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label>Họ và tên<input type="text" name="name" autocomplete="name" maxlength="120" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Nguyễn Văn An"></label>
        <label>Email<input type="email" name="email" autocomplete="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="ban@email.com"></label>
        <label>Mật khẩu<input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="200" required placeholder="Ít nhất 8 ký tự"><small>Mật khẩu cần có ít nhất 8 ký tự.</small></label>
        <button class="button button-dark auth-submit" type="submit">Tạo tài khoản <span>→</span></button>
    </form>
    <p class="auth-switch">Đã có tài khoản? <a href="<?= e(url('/dang-nhap')) ?>">Đăng nhập</a></p>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
