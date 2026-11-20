<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = 'Đặt lịch tư vấn';
$success = $success ?? null;
$error = $error ?? null;
ob_start();
?>
<section class="section advice-page">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow"><span></span> ĐẶT LỊCH TƯ VẤN</span><h2>Chúng tôi sẽ hỗ trợ <em>theo nhu cầu của bạn</em></h2><p>Điền đầy đủ thông tin để chuyên viên liên hệ tư vấn xe mới, lái thử hoặc phương án tài chính.</p></div></div>
        <?php if ($success): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e(url('/tu-van')) ?>" class="advice-form">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Họ và tên<input name="customer_name" maxlength="120" required value="<?= e($_SESSION['user']['name'] ?? '') ?>" placeholder="Nguyễn Văn An"></label>
            <label>Số điện thoại<input name="phone" type="tel" maxlength="30" required placeholder="0901 234 567"></label>
            <label>Email (không bắt buộc)<input name="email" type="email" maxlength="190" value="<?= e($_SESSION['user']['email'] ?? '') ?>" placeholder="ban@email.com"></label>
            <label>Dòng xe quan tâm<input name="car_interest" maxlength="180" placeholder="Ví dụ: SUV 7 chỗ, Mercedes-Benz GLC"></label>
            <label>Nội dung cần tư vấn<textarea name="message" rows="7" maxlength="2000" required placeholder="Nhu cầu sử dụng, ngân sách, thời gian mua xe, yêu cầu lái thử hoặc trả góp..."></textarea></label>
            <button class="button button-dark" type="submit">Gửi yêu cầu tư vấn <span>↗</span></button>
        </form>
    </div>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
