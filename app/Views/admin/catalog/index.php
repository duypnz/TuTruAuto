<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;

$title = $title ?? 'Hãng xe & kiểu dáng';
$options = $options ?? [];
$flash = $flash ?? null;
$groups = [
    'brand' => 'Thương hiệu xe',
    'body_type' => 'Kiểu dáng xe',
];
ob_start();
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> DANH MỤC KHO XE</span><h1>Hãng xe & kiểu dáng</h1><p>Thêm lựa chọn để dùng trong bộ lọc website và biểu mẫu quản lý xe.</p></div><span class="admin-badge"><?= count($options) ?> lựa chọn</span></div>
    <?php if (is_string($flash) && $flash !== ''): ?><div class="profile-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <div class="admin-panel">
        <h2>Thêm lựa chọn mới</h2>
        <form method="post" action="<?= e(url('/admin/danh-muc')) ?>" class="admin-inline-form">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <label>Loại danh mục<select name="option_type" required><option value="brand">Thương hiệu xe</option><option value="body_type">Kiểu dáng xe</option></select></label>
            <label>Tên lựa chọn<input name="name" maxlength="80" required placeholder="Ví dụ: Subaru hoặc Crossover"></label>
            <button class="button button-dark" type="submit">Thêm danh mục</button>
        </form>
    </div>
    <div class="catalog-option-groups">
        <?php foreach ($groups as $type => $label): ?>
            <?php $groupOptions = array_values(array_filter($options, static fn (array $option): bool => $option['option_type'] === $type)); ?>
            <section class="admin-panel"><h2><?= e($label) ?></h2>
                <?php if ($groupOptions): ?><ul class="catalog-option-list"><?php foreach ($groupOptions as $option): ?><li><?= e($option['name']) ?><span><?= (int) $option['is_active'] === 1 ? 'Đang dùng' : 'Đã ẩn' ?></span></li><?php endforeach; ?></ul>
                <?php else: ?><p>Chưa có danh mục.</p><?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
