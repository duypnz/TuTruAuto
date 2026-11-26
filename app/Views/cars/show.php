<?php
declare(strict_types=1);
$title = $car['name'];
$modelTypes = [
    'mercedes-benz-glc-300' => 'luxury-suv',
    'bmw-530i-m-sport' => 'sport-sedan',
    'volvo-xc60-recharge' => 'luxury-suv',
    'porsche-macan' => 'luxury-suv',
    'vinfast-vf-9-plus' => 'boxy-off-roader',
    'toyota-alphard-luxury' => 'luxury-limousine',
    'lexus-rx-350-premium' => 'luxury-suv',
    'toyota-camry-hev-premium' => 'executive-sedan',
    'ford-everest-titanium' => 'boxy-off-roader',
    'ford-ranger-wildtrak-2025' => 'boxy-off-roader',
    'hyundai-tucson-2.0' => 'luxury-suv',
    'mazda-cx-5-premium' => 'luxury-suv',
    'mazda3-sport-15-luxury' => 'hot-hatchback',
    'honda-cr-v-l' => 'luxury-suv',
    'honda-cr-v-ehev-rs' => 'luxury-suv',
    'porsche-718-cayman-2025' => 'mid-engine-supercar',
    'apollo-intensa-emozione' => 'rear-engine-sports-coupe',
    'lamborghini-revuelto' => 'mid-engine-supercar',
];
$modelType = $modelTypes[(string) ($car['slug'] ?? '')] ?? '';
$modelUrl = $modelType !== '' ? url('/public/assets/models/realistic/' . $modelType . '.glb') : '';
$details = json_decode((string) ($car['details_json'] ?? ''), true);
$priceOnRequest = is_array($details) && !empty($details['price_on_request']);
ob_start(); ?>
<section class="detail"><div class="container"><div class="breadcrumbs"><a href="<?= e(url('/')) ?>">Trang chủ</a><span>›</span><a href="<?= e(url('/xe')) ?>">Bộ sưu tập xe</a><span>›</span><?= e($car['name']) ?></div><div class="detail-grid"><div><div class="detail-image"><img src="<?= e(image_src($car['image_url'])) ?>" alt="<?= e($car['name']) ?>"><span class="image-badge"><?= e($car['badge'] ?: 'Sẵn sàng giao') ?></span></div>
<?php if ($modelUrl !== ''): ?>
    <button class="button button-outline car-3d-open" type="button" data-open-car-3d aria-expanded="false">Xem mô hình 3D 360°</button>
    <section class="car-3d-viewer" aria-label="Mô hình 3D xe" data-car-3d-panel hidden>
        <div class="car-3d-heading"><div><span class="eyebrow"><span></span> TRẢI NGHIỆM TƯƠNG TÁC</span><h2>Xem xe 3D</h2><p>Kéo để xoay · Cuộn để phóng to</p></div><span aria-hidden="true">360°</span></div>
        <div class="car-3d-modes" role="group" aria-label="Chọn góc xem mô hình">
            <button type="button" class="is-active" data-car-model-mode data-model-src="<?= e($modelUrl) ?>" data-model-view="exterior" aria-pressed="true">Ngoại thất</button>
            <button type="button" data-car-model-mode data-model-src="<?= e($modelUrl) ?>" data-model-view="interior" aria-pressed="false">Nội thất</button>
        </div>
        <model-viewer id="car-3d-model" data-model-src="<?= e($modelUrl) ?>" alt="Mô hình 3D <?= e($car['name']) ?>" camera-controls touch-action="pan-y" auto-rotate shadow-intensity="1" exposure="1.15" environment-image="neutral" camera-target="0m 0.8m 0m" min-camera-orbit="auto auto 0m" loading="lazy"></model-viewer>
        <p class="model-credit">Mẫu 3D đại diện cho kiểu dáng xe · <a href="https://vinish.dev/free-3d-models/realistic-cars" target="_blank" rel="noopener">vinish.dev</a> · CC BY 4.0</p>
    </section>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>
<?php endif; ?>
</div><div class="detail-info"><div class="eyebrow"><span></span> <?= e($car['brand']) ?> · <?= e($car['year']) ?></div><h1><?= e($car['name']) ?></h1><div class="detail-price"><?= $priceOnRequest ? 'Liên hệ báo giá' : money($car['price']) ?> <small><?= $priceOnRequest ? 'Theo báo giá showroom' : 'Giá niêm yết' ?></small></div><div class="detail-actions"><?php if (isCustomerLoggedIn()): ?><form method="post" action="<?= e(url('/yeu-thich/' . $car['slug'] . '/toggle')) ?>" class="favorite-form favorite-form-detail">
        <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
        <button type="submit" class="favorite-toggle <?= isFavorite($car['slug']) ? 'is-active' : '' ?>" aria-label="<?= isFavorite($car['slug']) ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>" title="<?= isFavorite($car['slug']) ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>"><?= isFavorite($car['slug']) ? '♥' : '♡' ?></button>
    </form><?php else: ?><a class="favorite-toggle favorite-form-detail" href="<?= e(url('/dang-nhap')) ?>" aria-label="Đăng nhập để thêm xe yêu thích" title="Đăng nhập để thêm xe yêu thích">♡</a><?php endif; ?><a class="button button-dark full-button" href="tel:19006868">Đăng ký tư vấn & lái thử <span>↗</span></a></div><p class="detail-description"><?= e($car['description']) ?></p><div class="detail-specs"><div><small>Kiểu dáng</small><strong><?= e($car['body_type']) ?></strong></div><div><small>Nhiên liệu</small><strong><?= e($car['fuel']) ?></strong></div><div><small>Hộp số</small><strong><?= e($car['transmission']) ?></strong></div><div><small>Năm sản xuất</small><strong><?= e($car['year']) ?></strong></div></div><div class="detail-note">✓ Tư vấn miễn phí &nbsp; ✓ Hỗ trợ trả góp &nbsp; ✓ Giao xe toàn quốc</div></div></div></div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php';
