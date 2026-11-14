<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
use App\Core\Csrf;
$title = 'Đăng nhập';
ob_start();
?>
<section class="auth-section"><div class="auth-card"><span class="eyebrow"><span></span> CHÀO MỪNG BẠN TRỞ LẠI</span><h1>Đăng nhập</h1><p class="auth-intro">Đăng nhập để tiếp tục đồng hành cùng Tu Tru Auto.</p>
    <?php if ($error): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/dang-nhap')) ?>" class="auth-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label>Email<input type="email" name="email" autocomplete="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="ban@email.com"></label>
        <label>Mật khẩu<input type="password" name="password" autocomplete="current-password" required placeholder="Nhập mật khẩu"></label>
        <button class="button button-dark auth-submit" type="submit">Đăng nhập <span>→</span></button>
    </form>
    <p class="auth-switch">Chưa có tài khoản? <a href="<?= e(url('/dang-ky')) ?>">Đăng ký ngay</a></p>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
