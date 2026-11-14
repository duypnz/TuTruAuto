<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';
use App\Core\Csrf;
$title = $title ?? 'Xe';
$car = $car ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$csrf = $csrf ?? Csrf::token();
ob_start();
$val = static function (string $key, string $default = '') use ($car, $old): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if (is_array($car) && array_key_exists($key, $car) && $car[$key] !== null) {
        return (string) $car[$key];
    }
    return $default;
};
$checked = static function (string $key) use ($car, $old): string {
    $v = '';
    if (array_key_exists($key, $old)) {
        $v = (string) $old[$key];
    } elseif (is_array($car) && array_key_exists($key, $car)) {
        $v = (string) $car[$key];
    }
    return $v === '1' || $v === 'true' || $v === 'on' ? 'checked' : '';
};
?>
<section class="admin-page"><div class="container">
    <div class="admin-heading"><div><span class="eyebrow"><span></span> KHU VỰC QUẢN TRỊ</span><h1><?= e($title) ?></h1><p>Điền thông tin xe. Các trường có dấu * là bắt buộc.</p></div><span class="admin-badge">ADMIN</span></div>
    <?php if (isset($errors['_form'])): ?><div class="auth-alert" role="alert"><?= e($errors['_form']) ?></div><?php endif; ?>
    <div class="admin-panel">
        <form method="post" action="<?= e($car ? url('/admin/xe/' . (int)$car['id'] . '/cap-nhat') : url('/admin/xe')) ?>" enctype="multipart/form-data" class="auth-form">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Tên xe *<input type="text" name="name" maxlength="180" required value="<?= e($val('name')) ?>"><?php if (isset($errors['name'])): ?><small style="color:#c0392b;"><?= e($errors['name']) ?></small><?php endif; ?></label>
            <label>Thương hiệu *<select name="brand" required>
                <option value="">-- Chọn --</option>
                <?php foreach (['Toyota','Mercedes-Benz','BMW','VinFast','Volvo','Lexus','Hyundai','Honda','Ford','Mazda'] as $b): ?>
                    <option value="<?= e($b) ?>" <?= $val('brand') === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                <?php endforeach; ?>
            </select><?php if (isset($errors['brand'])): ?><small style="color:#c0392b;"><?= e($errors['brand']) ?></small><?php endif; ?></label>
            <label>Năm sản xuất *<input type="number" name="year" min="1990" max="2100" required value="<?= e($val('year')) ?>"><?php if (isset($errors['year'])): ?><small style="color:#c0392b;"><?= e($errors['year']) ?></small><?php endif; ?></label>
            <label>Kiểu dáng *<select name="body_type" required>
                <option value="">-- Chọn --</option>
                <?php foreach (['SUV','Sedan','MPV','Coupe','Hatchback','Pickup'] as $t): ?>
                    <option value="<?= e($t) ?>" <?= $val('body_type') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select><?php if (isset($errors['body_type'])): ?><small style="color:#c0392b;"><?= e($errors['body_type']) ?></small><?php endif; ?></label>
            <label>Nhiên liệu *<select name="fuel" required>
                <option value="">-- Chọn --</option>
                <?php foreach (['Xăng','Hybrid','Điện','Dầu'] as $f): ?>
                    <option value="<?= e($f) ?>" <?= $val('fuel') === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                <?php endforeach; ?>
            </select><?php if (isset($errors['fuel'])): ?><small style="color:#c0392b;"><?= e($errors['fuel']) ?></small><?php endif; ?></label>
            <label>Hộp số *<input type="text" name="transmission" maxlength="50" required value="<?= e($val('transmission')) ?>" placeholder="Tự động 8 cấp"><?php if (isset($errors['transmission'])): ?><small style="color:#c0392b;"><?= e($errors['transmission']) ?></small><?php endif; ?></label>
            <label>Giá (VND) *<input type="number" name="price" min="1" required value="<?= e($val('price')) ?>"><?php if (isset($errors['price'])): ?><small style="color:#c0392b;"><?= e($errors['price']) ?></small><?php endif; ?></label>
            <label>Trạng thái *<select name="status">
                <?php foreach (['available'=>'Đang bán','reserved'=>'Đã đặt cọc','sold'=>'Đã bán','hidden'=>'Ẩn'] as $k=>$v): ?>
                    <option value="<?= e($k) ?>" <?= $val('status', 'available') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Badge<input type="text" name="badge" maxlength="80" value="<?= e($val('badge')) ?>" placeholder="Mới, Hot, Khuyến mãi..."><?php if (isset($errors['badge'])): ?><small style="color:#c0392b;"><?= e($errors['badge']) ?></small><?php endif; ?></label>
            <label>Mô tả<textarea name="description" rows="4"><?= e($val('description')) ?></textarea></label>
            <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="featured" value="1" <?= $checked('featured') ?>> Xe nổi bật</label>
            <label>Ảnh xe <?= $car ? '(để trống nếu giữ ảnh cũ)' : '*' ?>
                <?php if ($car): ?><div style="margin:6px 0;"><img src="<?= e(image_src($car['image_url'])) ?>" alt="" style="width:120px;height:80px;object-fit:cover;border-radius:6px;"></div><?php endif; ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= $car ? '' : 'required' ?>>
                <?php if (isset($errors['image'])): ?><small style="color:#c0392b;"><?= e($errors['image']) ?></small><?php endif; ?>
            </label>
            <button class="button button-dark auth-submit" type="submit"><?= $car ? 'Cập nhật xe' : 'Thêm xe' ?> <span>→</span></button>
        </form>
        <p style="margin-top:12px;"><a href="<?= e(url('/admin/xe')) ?>">← Quay lại danh sách</a></p>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>