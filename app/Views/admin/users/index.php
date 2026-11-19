<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;

$title = $title ?? 'Quản lý tài khoản';
$users = $users ?? [];
$flash = $flash ?? null;
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> QUẢN TRỊ NGƯỜI DÙNG</div><h1>Quản lý tài khoản</h1><p>Tạo tài khoản, thay đổi quyền truy cập và khóa các tài khoản cần thiết.</p></div><span class="admin-badge"><?= count($users) ?> tài khoản</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <div class="admin-panel">
        <h2>Tạo tài khoản</h2>
        <p>Mật khẩu ban đầu chỉ dùng một lần; chia sẻ cho người dùng qua kênh an toàn.</p>
        <form method="post" action="<?= e(url('/admin/tai-khoan')) ?>" class="admin-create-user-form">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <label>Họ tên<input name="name" maxlength="120" required></label>
            <label>Email<input name="email" type="email" maxlength="190" required></label>
            <label>Số điện thoại<input name="phone" type="tel" maxlength="30"></label>
            <label>Mật khẩu ban đầu<input name="password" type="password" minlength="8" maxlength="200" required></label>
            <label>Quyền<select name="role"><option value="customer">Khách hàng</option><option value="admin">Quản trị viên</option></select></label>
            <button class="button button-dark" type="submit">Tạo tài khoản</button>
        </form>
    </div>
    <div class="admin-panel account-table-panel"><h2>Danh sách tài khoản</h2>
        <?php if ($users): ?>
            <div class="admin-table-scroll"><table class="admin-data-table">
                <thead><tr><th>Tài khoản</th><th>Ngày tạo</th><th>Quyền và trạng thái</th><th></th></tr></thead>
                <tbody><?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= e($user['name']) ?><?= (int) $user['id'] === (int) $_SESSION['user']['id'] ? ' · Bạn' : '' ?></strong><small><?= e($user['email']) ?><?= !empty($user['phone']) ? ' · ' . e($user['phone']) : '' ?></small></td>
                        <td><?= e(date('d/m/Y', strtotime($user['created_at']))) ?></td>
                        <td><form method="post" action="<?= e(url('/admin/tai-khoan/' . (int) $user['id'] . '/cap-nhat')) ?>" class="admin-access-form">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <select name="role" aria-label="Quyền tài khoản"><option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Khách hàng</option><option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Quản trị viên</option></select>
                            <select name="status" aria-label="Trạng thái tài khoản"><?php foreach (['active' => 'Hoạt động', 'inactive' => 'Không hoạt động', 'locked' => 'Đã khóa'] as $value => $label): ?><option value="<?= e($value) ?>" <?= $user['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
                            <button class="button button-outline" type="submit">Lưu</button>
                        </form></td><td></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php else: ?><p>Chưa có tài khoản.</p><?php endif; ?>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
