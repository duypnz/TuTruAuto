<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;
$title = $title ?? 'Quản lý xe';
$flash = $flash ?? null;
$cars = $cars ?? [];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> KHU VỰC QUẢN TRỊ</span><h1>Quản lý xe</h1><p>Thêm, sửa, xóa tin xe trong hệ thống.</p></div><span class="admin-badge">ADMIN</span></div>
    <?php if (is_string($flash)): ?><div class="auth-alert" role="status" style="margin-bottom:16px;"><?= e($flash) ?></div><?php endif; ?>
    <div class="admin-panel" style="margin-bottom:20px;"><a class="button button-dark" href="<?= e(url('/admin/xe/them')) ?>">+ Thêm xe mới</a> <a class="button" href="<?= e(url('/admin')) ?>" style="margin-left:8px;">← Dashboard</a></div>
    <div class="admin-panel">
        <?php if ($cars): ?>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th style="padding:8px;">Ảnh</th><th style="padding:8px;">Tên xe</th><th style="padding:8px;">Hãng</th><th style="padding:8px;">Giá</th><th style="padding:8px;">Còn tại cửa hàng</th><th style="padding:8px;">Thư viện</th><th style="padding:8px;">Trạng thái</th><th style="padding:8px;">Nổi bật</th><th style="padding:8px;">Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($cars as $car): ?>
                <?php $gallery = json_decode((string) ($car['gallery_json'] ?? ''), true); $galleryCount = is_array($gallery) ? count(array_filter($gallery, static fn (mixed $image): bool => is_string($image) && $image !== $car['image_url'])) : 0; ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:8px;"><img src="<?= e(image_src($car['image_url'])) ?>" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:6px;"></td>
                    <td style="padding:8px;"><strong><?= e($car['name']) ?></strong><br><small style="color:#888;"><?= e($car['slug']) ?></small></td>
                    <td style="padding:8px;"><?= e($car['brand']) ?> · <?= e($car['year']) ?></td>
                    <td style="padding:8px;"><?= money($car['price']) ?></td>
                    <td style="padding:8px;"><strong><?= $car['stock_quantity'] === null ? 'Chưa kiểm kê' : (int) $car['stock_quantity'] . ' xe' ?></strong><br><small>Nhận xe sau <?= (int) ($car['lead_time_days'] ?? 21) ?> ngày nếu hết hàng</small></td>
                    <td style="padding:8px;"><?= $galleryCount ?> ảnh phụ<br><small><?= $galleryCount >= 10 ? 'Đã đủ ảnh phụ' : 'Cần thêm ' . (10 - $galleryCount) . ' ảnh' ?></small></td>
                    <td style="padding:8px;"><?= e($car['status']) ?></td>
                    <td style="padding:8px;"><?= (int)$car['featured'] === 1 ? '★' : '' ?></td>
                    <td style="padding:8px;white-space:nowrap;">
                        <a class="button" href="<?= e(url('/admin/xe/' . (int)$car['id'] . '/sua')) ?>">Sửa</a>
                        <form method="post" action="<?= e(url('/admin/xe/' . (int)$car['id'] . '/xoa')) ?>" style="display:inline;" onsubmit="return confirm('Xóa xe này?');">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <button type="submit" class="button" style="background:#c0392b;color:#fff;border-color:#c0392b;">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Chưa có xe nào. <a href="<?= e(url('/admin/xe/them')) ?>">Thêm xe đầu tiên</a>.</p>
        <?php endif; ?>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
