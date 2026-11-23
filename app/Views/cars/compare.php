<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'So sánh xe';
$selectedCars = $selectedCars ?? [];
$availableCars = $availableCars ?? [];
$selectedIds = array_map('intval', $selectedIds ?? []);
$comparisonRows = [
    'brand' => 'Thương hiệu',
    'body_type' => 'Kiểu dáng',
    'year' => 'Năm sản xuất',
    'condition_type' => 'Tình trạng',
    'price' => 'Giá niêm yết',
    'stock_quantity' => 'Xe hiện có tại cửa hàng',
    'fuel' => 'Nhiên liệu',
    'transmission' => 'Hộp số',
];
$specRows = [
    'engine' => 'Động cơ',
    'displacement' => 'Dung tích',
    'power' => 'Công suất',
    'torque' => 'Mô-men xoắn',
    'consumption' => 'Mức tiêu hao',
    'dimensions' => 'Dài × rộng × cao',
    'wheelbase' => 'Chiều dài cơ sở',
    'ground_clearance' => 'Khoảng sáng gầm',
    'seats' => 'Số chỗ',
    'doors' => 'Số cửa',
    'tank' => 'Bình xăng / pin',
    'trunk' => 'Dung tích cốp',
    'origin' => 'Xuất xứ',
];
$conditionLabels = ['new' => 'Xe mới'];
$detailsByCar = [];
foreach ($selectedCars as $car) {
    $decoded = json_decode((string) ($car['details_json'] ?? ''), true);
    $detailsByCar[(int) $car['id']] = is_array($decoded) ? $decoded : [];
}
ob_start();
?>
<section class="section compare-page"><div class="container">
    <div class="section-heading compare-heading"><div><span class="eyebrow"><span></span> SO SÁNH CẤU HÌNH XE</span><h2>Chọn xe phù hợp với bạn</h2><p>Chọn từ 2 đến 3 xe đang bán để đối chiếu thông số và giá.</p></div><a class="outline-link" href="<?= e(url('/xe')) ?>">← Quay lại bộ sưu tập</a></div>
    <form method="get" action="<?= e(url('/so-sanh')) ?>" class="compare-select-form" data-compare-select-form>
        <?php for ($slot = 0; $slot < 3; $slot++): ?>
            <label>Xe <?= $slot + 1 ?><?= $slot < 2 ? ' *' : ' (tùy chọn)' ?>
                <select name="ids[]" <?= $slot < 2 ? 'required' : '' ?> data-compare-select>
                    <option value=""><?= $slot < 2 ? 'Chọn xe' : 'Không chọn xe thứ ba' ?></option>
                    <?php foreach ($availableCars as $availableCar): ?>
                        <?php $carId = (int) $availableCar['id']; ?>
                        <option value="<?= $carId ?>" <?= ($selectedIds[$slot] ?? 0) === $carId ? 'selected' : '' ?>>
                            <?= e($availableCar['name']) ?> · <?= e($availableCar['brand']) ?> · <?= money($availableCar['discount_price'] ?: $availableCar['price']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endfor; ?>
        <button class="button button-dark" type="submit">So sánh xe</button>
    </form>

    <?php if (count($selectedCars) >= 2): ?>
        <div class="comparison-table-wrap">
            <table class="comparison-table">
                <thead><tr><th>Thông tin</th><?php foreach ($selectedCars as $car): ?><th><img src="<?= e(image_src($car['image_url'])) ?>" alt="<?= e($car['name']) ?>" loading="lazy"><strong><?= e($car['name']) ?></strong><a href="<?= e(url('/xe/' . $car['slug'])) ?>">Xem chi tiết ↗</a></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($comparisonRows as $field => $label): ?>
                        <tr><th><?= e($label) ?></th><?php foreach ($selectedCars as $car): ?>
                            <?php $value = match ($field) {
                                'condition_type' => $conditionLabels[$car[$field] ?? 'new'] ?? 'Chưa cập nhật',
                                'price' => money($car['discount_price'] ?: $car['price']),
                                'stock_quantity' => $car[$field] === null
                                    ? 'Liên hệ để xác nhận'
                                    : ((int) $car[$field] > 0
                                        ? (int) $car[$field] . ' xe'
                                        : 'Hết hàng · có thể đặt trước'),
                                default => $car[$field] ?? 'Chưa cập nhật',
                            }; ?>
                            <td><?= e($value) ?></td>
                        <?php endforeach; ?></tr>
                    <?php endforeach; ?>
                    <?php foreach ($specRows as $field => $label): ?>
                        <tr><th><?= e($label) ?></th><?php foreach ($selectedCars as $car): ?>
                            <?php $specs = $detailsByCar[(int) $car['id']]['specs'] ?? []; ?>
                            <?php $value = is_array($specs) ? ($specs[$field] ?? 'Chưa cập nhật') : 'Chưa cập nhật'; ?>
                            <td><?= e(is_scalar($value) ? $value : 'Chưa cập nhật') ?></td>
                        <?php endforeach; ?></tr>
                    <?php endforeach; ?>
                    <tr class="comparison-deposit"><th>Tiền cọc (10%)</th><?php foreach ($selectedCars as $car): ?><td><?= money((int) round((int) ($car['discount_price'] ?: $car['price']) * 0.1)) ?></td><?php endforeach; ?></tr>
                </tbody>
            </table>
        </div>
    <?php elseif (count($selectedCars) === 1): ?>
        <div class="compare-prompt"><strong>Đã chọn <?= e($selectedCars[0]['name']) ?>.</strong><span>Chọn thêm ít nhất một xe rồi bấm “So sánh xe”.</span></div>
    <?php else: ?>
        <div class="compare-prompt"><strong>Chưa chọn đủ xe để so sánh.</strong><span>Chọn hai hoặc ba mẫu xe trong danh sách phía trên.</span></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
