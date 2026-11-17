<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = 'Thông tin hồ sơ';
ob_start();
?>
<section class="auth-section profile-section"><div class="auth-card profile-card">
    <span class="eyebrow"><span></span> TÀI KHOẢN KHÁCH HÀNG</span>
    <h1>Thông tin hồ sơ</h1>
    <p class="auth-intro">Cập nhật thông tin liên hệ để Tu Tru Auto hỗ trợ bạn tốt hơn.</p>
    <?php if ($error): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/tai-khoan/ho-so')) ?>" class="auth-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label>Họ và tên<input type="text" name="name" autocomplete="name" maxlength="120" required value="<?= e($user['name'] ?? '') ?>"></label>
        <label>Email<input type="email" value="<?= e($user['email'] ?? '') ?>" readonly aria-describedby="email-note"><small id="email-note">Email đăng nhập không thể chỉnh sửa tại đây.</small></label>
        <label>Số điện thoại<input type="tel" name="phone" autocomplete="tel" maxlength="30" value="<?= e($user['phone'] ?? '') ?>" placeholder="0901 234 567"></label>
        <label>Địa chỉ<input type="text" name="address" autocomplete="street-address" maxlength="255" value="<?= e($user['address'] ?? '') ?>" placeholder="Quận, thành phố"></label>
        <button class="button button-dark auth-submit" type="submit">Lưu thông tin <span>→</span></button>
    </form>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
