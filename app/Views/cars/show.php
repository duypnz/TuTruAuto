<?php
declare(strict_types=1);

$title = $car['name'];
$details = is_array($car['details']) ? $car['details'] : [];
$specs = is_array($details['specs'] ?? null) ? $details['specs'] : [];
$variants = is_array($details['variants'] ?? null) ? $details['variants'] : [];
if ($variants === []) {
    $variants = [
        ['name' => 'Base', 'price' => 0],
        ['name' => 'Premium', 'price' => 150000000],
        ['name' => 'Luxury', 'price' => 300000000],
    ];
}
$colors = is_array($details['colors'] ?? null) ? $details['colors'] : [];
$exteriorColors = is_array($colors['exterior'] ?? null) ? $colors['exterior'] : ['Trắng', 'Đen', 'Bạc', 'Xanh'];
$interiorColors = is_array($colors['interior'] ?? null) ? $colors['interior'] : ['Đen', 'Be', 'Nâu'];
$options = is_array($details['options'] ?? null) ? $details['options'] : [];
$price = (int) ($car['discount_price'] ?: $car['price']);
$stockLabels = ['available' => 'Còn hàng', 'reserved' => 'Đang giữ chỗ', 'sold' => 'Đã bán'];
$stockLabel = $stockLabels[$car['status']] ?? 'Liên hệ showroom';
$conditionLabel = ($car['condition_type'] ?? 'new') === 'used' ? 'Xe đã qua sử dụng' : 'Xe mới';
$specRows = [
    'model_line' => 'Dòng xe',
    'category' => 'Danh mục',
    'engine' => 'Động cơ',
    'displacement' => 'Dung tích động cơ',
    'power' => 'Công suất tối đa',
    'torque' => 'Mô-men xoắn',
    'transmission' => 'Hộp số',
    'fuel' => 'Nhiên liệu',
    'consumption' => 'Mức tiêu hao',
    'dimensions' => 'Dài × rộng × cao',
    'wheelbase' => 'Chiều dài cơ sở',
    'ground_clearance' => 'Khoảng sáng gầm',
    'seats' => 'Số chỗ ngồi',
    'doors' => 'Số cửa',
    'tank' => 'Dung tích bình xăng/pin',
    'trunk' => 'Dung tích cốp',
    'origin' => 'Xuất xứ',
];
if (($car['condition_type'] ?? 'new') === 'used') {
    $specRows += [
        'mileage' => 'Số km đã đi',
        'owners' => 'Số đời chủ',
        'license_plate' => 'Biển số',
        'inspection' => 'Đăng kiểm đến',
    ];
}
$features = [
    'safety' => ['title' => 'An toàn', 'icon' => '⛨'],
    'comfort' => ['title' => 'Tiện nghi', 'icon' => '✦'],
];
$embedUrl = '';
if (!empty($car['video_url'])) {
    $videoUrl = (string) $car['video_url'];
    $videoHost = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
    if ($videoHost === 'youtu.be') {
        $videoId = trim((string) parse_url($videoUrl, PHP_URL_PATH), '/');
        $embedUrl = $videoId !== '' ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($videoId) : '';
    } elseif (in_array($videoHost, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
        parse_str((string) parse_url($videoUrl, PHP_URL_QUERY), $videoQuery);
        $videoId = (string) ($videoQuery['v'] ?? '');
        $embedUrl = $videoId !== '' ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($videoId) : '';
    }
}
ob_start();
?>
<section class="detail">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Điều hướng">
            <a href="<?= e(url('/')) ?>">Trang chủ</a><span>›</span>
            <a href="<?= e(url('/xe')) ?>">Bộ sưu tập xe</a><span>›</span>
            <?= e($car['name']) ?>
        </nav>

        <?php if (!empty($success)): ?><div class="profile-success" role="status"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="auth-alert" role="alert"><?= e($error) ?></div><?php endif; ?>

        <div class="detail-grid">
            <div class="detail-gallery">
                <div class="detail-image">
                    <img id="car-main-image" src="<?= e(image_src($car['gallery'][0])) ?>" alt="<?= e($car['name']) ?>">
                    <span class="image-badge"><?= e($car['badge'] ?: $stockLabel) ?></span>
                </div>
                <div class="detail-thumbnails" aria-label="Thư viện ảnh xe">
                    <?php foreach ($car['gallery'] as $index => $image): ?>
                        <button class="detail-thumbnail <?= $index === 0 ? 'is-active' : '' ?>" type="button"
                                data-image="<?= e(image_src($image)) ?>" aria-label="Xem ảnh <?= $index + 1 ?>"
                                aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                            <img src="<?= e(image_src($image)) ?>" alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php if ($embedUrl !== ''): ?>
                    <div class="detail-video"><h2>Video giới thiệu</h2>
                        <iframe src="<?= e($embedUrl) ?>" title="Video giới thiệu <?= e($car['name']) ?>"
                                loading="lazy" allowfullscreen></iframe>
                    </div>
                <?php elseif (!empty($car['video_url'])): ?>
                    <a class="detail-video-link" href="<?= e($car['video_url']) ?>" target="_blank" rel="noopener">▶ Xem video giới thiệu</a>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <div class="eyebrow"><span></span><?= e($car['brand']) ?> · <?= e($car['year']) ?> · <?= e($conditionLabel) ?></div>
                <h1><?= e($car['name']) ?></h1>
                <div class="detail-meta-line">
                    <span>Mã xe <strong><?= e($car['car_code'] ?: 'TTA-' . str_pad((string) $car['id'], 6, '0', STR_PAD_LEFT)) ?></strong></span>
                    <span><?= e($specs['category'] ?? $car['body_type']) ?></span>
                    <span class="stock-state <?= $car['status'] === 'available' ? 'is-available' : '' ?>"><?= e($stockLabel) ?></span>
                </div>

                <div class="detail-price" id="detail-price" data-base-price="<?= e($price) ?>">
                    <?= money($price) ?>
                    <?php if (!empty($car['discount_price'])): ?>
                        <del><?= money($car['price']) ?></del>
                        <span class="sale-label">Giá ưu đãi</span>
                    <?php else: ?>
                        <small>Giá niêm yết</small>
                    <?php endif; ?>
                </div>

                <div class="detail-quick-specs">
                    <span>⚙ <?= e($car['transmission']) ?></span>
                    <span>◉ <?= e($car['fuel']) ?></span>
                    <span>↗ <?= e($car['body_type']) ?></span>
                </div>

                <div class="detail-social-actions">
                    <?php if (isCustomerLoggedIn()): ?>
                        <form method="post" action="<?= e(url('/yeu-thich/' . $car['slug'] . '/toggle')) ?>">
                            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
                            <button type="submit" class="favorite-toggle" aria-label="Yêu thích xe"><?= isFavorite($car['slug']) ? '♥ Đã thích' : '♡ Yêu thích' ?></button>
                        </form>
                    <?php else: ?>
                        <a class="favorite-toggle" href="<?= e(url('/dang-nhap')) ?>">♡ Yêu thích</a>
                    <?php endif; ?>
                    <button class="detail-icon-action" type="button" data-compare-car="<?= e($car['id']) ?>" aria-pressed="false">⇄ So sánh</button>
                    <button class="detail-icon-action" type="button" data-share-car>↗ Chia sẻ</button>
                </div>

                <form id="car-order-form" class="car-order-form" method="post"
                      action="<?= e(url('/xe/' . $car['slug'] . '/mua')) ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <div class="variant-grid">
                        <label>Phiên bản
                            <select name="variant" id="car-variant">
                                <?php foreach ($variants as $index => $variant): if (!is_array($variant) || empty($variant['name'])) continue; ?>
                                    <option value="<?= e($variant['name']) ?>" data-delta="<?= e((string) (int) ($variant['price'] ?? 0)) ?>" <?= $index === 0 ? 'selected' : '' ?>>
                                        <?= e($variant['name']) ?><?= (int) ($variant['price'] ?? 0) > 0 ? ' (+' . e(money($variant['price'])) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Số lượng
                            <input name="quantity" type="number" min="1" max="5" value="1" required>
                        </label>
                    </div>

                    <fieldset class="color-picker">
                        <legend>Màu ngoại thất</legend>
                        <select name="exterior">
                            <?php foreach ($exteriorColors as $color): $colorName = is_array($color) ? (string) ($color['name'] ?? '') : (string) $color; ?>
                                <?php if ($colorName !== ''): ?><option value="<?= e($colorName) ?>"><?= e($colorName) ?></option><?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </fieldset>
                    <fieldset class="color-picker">
                        <legend>Màu nội thất</legend>
                        <select name="interior">
                            <?php foreach ($interiorColors as $color): $colorName = is_array($color) ? (string) ($color['name'] ?? '') : (string) $color; ?>
                                <?php if ($colorName !== ''): ?><option value="<?= e($colorName) ?>"><?= e($colorName) ?></option><?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </fieldset>

                    <?php if ($options !== []): ?>
                        <fieldset class="extra-options"><legend>Gói tùy chọn thêm</legend>
                            <?php foreach ($options as $option): if (!is_array($option) || empty($option['name'])) continue; ?>
                                <label><input type="checkbox" name="options[]" value="<?= e($option['name']) ?>" data-delta="<?= e((string) (int) ($option['price'] ?? 0)) ?>">
                                    <span><?= e($option['name']) ?></span>
                                    <strong><?= (int) ($option['price'] ?? 0) > 0 ? '+ ' . e(money($option['price'])) : 'Miễn phí' ?></strong>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>

                    <div class="voucher-row">
                        <label for="voucher-code">Mã giảm giá</label>
                        <div><input id="voucher-code" name="voucher_code" type="text" maxlength="40" placeholder="Nhập mã voucher">
                            <button type="button" id="check-voucher" data-endpoint="<?= e(url('/voucher/kiem-tra')) ?>">Kiểm tra</button></div>
                        <p id="voucher-result" role="status" aria-live="polite"></p>
                        <small class="voucher-hint">Mã mẫu TUTRU5: giảm 5% cho đơn từ 500 triệu đồng.</small>
                    </div>

                    <div class="purchase-actions">
                        <button class="button button-dark" name="action" value="cart" type="submit" <?= $car['status'] !== 'available' ? 'disabled' : '' ?>>Thêm vào giỏ hàng</button>
                        <button class="button button-light" name="action" value="buy" type="submit" <?= $car['status'] !== 'available' ? 'disabled' : '' ?>>Mua ngay</button>
                        <button class="button button-outline" name="action" value="deposit" type="submit" <?= $car['status'] !== 'available' ? 'disabled' : '' ?>>Đặt cọc</button>
                    </div>
                    <div class="contact-fields">
                        <label>Họ tên<input name="customer_name" maxlength="120" value="<?= e($_SESSION['user']['name'] ?? '') ?>" placeholder="Tên của bạn"></label>
                        <label>Số điện thoại<input name="phone" type="tel" maxlength="30" placeholder="Số điện thoại liên hệ"></label>
                    </div>
                    <label class="message-field">Ghi chú<textarea name="message" rows="2" maxlength="1000" placeholder="Thời gian lái thử hoặc yêu cầu thêm"></textarea></label>
                    <div class="purchase-secondary">
                        <button type="submit" name="action" value="test_drive">Đăng ký lái thử</button>
                        <button type="submit" name="action" value="quote">Yêu cầu báo giá</button>
                        <button type="submit" name="action" value="advice">Liên hệ tư vấn</button>
                    </div>
                </form>

                <p class="detail-note">✓ Tư vấn miễn phí &nbsp; ✓ Hỗ trợ trả góp &nbsp; ✓ Giao xe toàn quốc</p>
                <div class="policy-list">
                    <div><span>♧</span><p><strong>Bảo hành chính hãng</strong><small>Quyền lợi theo tiêu chuẩn của nhà sản xuất</small></p></div>
                    <div><span>⚙</span><p><strong>Bảo dưỡng định kỳ</strong><small>Hỗ trợ đặt lịch tại hệ thống dịch vụ</small></p></div>
                    <div><span>₫</span><p><strong>Hỗ trợ trả góp</strong><small>Tư vấn phương án tài chính phù hợp</small></p></div>
                    <div><span>↗</span><p><strong>Giao xe linh hoạt</strong><small>Liên hệ để kiểm tra khu vực giao xe</small></p></div>
                </div>
            </div>
        </div>

        <section class="specification-section" aria-labelledby="specifications-title">
            <div class="section-heading"><div><span class="eyebrow"><span></span> THÔNG TIN XE</span><h2 id="specifications-title">Thông số kỹ thuật</h2></div></div>
            <div class="specification-table">
                <?php foreach ($specRows as $key => $label): ?>
                    <?php $value = $specs[$key] ?? match ($key) {
                        'model_line' => $car['body_type'],
                        'transmission' => $car['transmission'],
                        'fuel' => $car['fuel'],
                        'category' => $car['body_type'],
                        default => 'Liên hệ để biết thêm',
                    }; ?>
                    <div><span><?= e($label) ?></span><strong><?= e($value) ?></strong></div>
                <?php endforeach; ?>
                <div><span>Hãng xe</span><strong><?= e($car['brand']) ?></strong></div>
                <div><span>Năm sản xuất</span><strong><?= e($car['year']) ?></strong></div>
                <div><span>Tình trạng</span><strong><?= e($conditionLabel) ?></strong></div>
                <div><span>Mã xe</span><strong><?= e($car['car_code'] ?: 'TTA-' . str_pad((string) $car['id'], 6, '0', STR_PAD_LEFT)) ?></strong></div>
            </div>
        </section>

        <section class="feature-section">
            <div class="section-heading"><div><span class="eyebrow"><span></span> TRANG BỊ</span><h2>An toàn & tiện nghi</h2></div></div>
            <div class="feature-columns">
                <?php foreach ($features as $key => $feature): ?>
                    <?php $items = is_array($details[$key] ?? null) ? $details[$key] : []; ?>
                    <article class="feature-card"><h3><span><?= e($feature['icon']) ?></span><?= e($feature['title']) ?></h3>
                        <?php if ($items): ?><ul><?php foreach ($items as $item): ?><li><span aria-hidden="true">✓</span><?= e($item) ?></li><?php endforeach; ?></ul>
                        <?php else: ?><p>Thông tin trang bị đang được cập nhật. Vui lòng liên hệ tư vấn để biết cấu hình chính xác.</p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="car-tabs" data-car-tabs>
            <div class="tab-list" role="tablist" aria-label="Thông tin chi tiết xe">
                <button type="button" role="tab" id="tab-description" aria-controls="panel-description" aria-selected="true">Mô tả chi tiết</button>
                <button type="button" role="tab" id="tab-highlights" aria-controls="panel-highlights" aria-selected="false">Điểm nổi bật</button>
                <button type="button" role="tab" id="tab-pros" aria-controls="panel-pros" aria-selected="false">Ưu & nhược điểm</button>
            </div>
            <div id="panel-description" class="tab-panel" role="tabpanel" aria-labelledby="tab-description">
                <p><?= nl2br(e($car['description'] ?: 'Liên hệ showroom để nhận thông tin chi tiết về mẫu xe.')) ?></p>
            </div>
            <div id="panel-highlights" class="tab-panel" role="tabpanel" aria-labelledby="tab-highlights" hidden>
                <ul><?php foreach ((array) ($details['highlights'] ?? ['Thiết kế hiện đại', 'Tư vấn cấu hình và chi phí minh bạch', 'Hỗ trợ lái thử tại showroom']) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul>
            </div>
            <div id="panel-pros" class="tab-panel" role="tabpanel" aria-labelledby="tab-pros" hidden>
                <div class="pros-cons">
                    <div><h3>Ưu điểm</h3><ul><?php foreach ((array) ($details['pros'] ?? ['Thiết kế và trang bị phù hợp nhu cầu sử dụng']) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul></div>
                    <div><h3>Điểm cần cân nhắc</h3><ul><?php foreach ((array) ($details['cons'] ?? ['Vui lòng trải nghiệm và so sánh phiên bản trước khi chọn mua']) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul></div>
                </div>
            </div>
        </section>

        <section class="reviews-section" id="danh-gia">
            <div class="section-heading"><div><span class="eyebrow"><span></span> CHIA SẺ TRẢI NGHIỆM</span><h2>Đánh giá của khách hàng</h2></div>
                <div class="review-score"><strong><?= number_format((float) $reviewSummary['average'], 1) ?></strong><span>★</span><small><?= (int) $reviewSummary['total'] ?> đánh giá</small></div>
            </div>
            <?php if (isCustomerLoggedIn()): ?>
                <form class="review-form" method="post" action="<?= e(url('/xe/' . $car['slug'] . '/danh-gia')) ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <label>Chấm điểm<select name="rating" required><option value="">Chọn số sao</option><?php for ($rating = 5; $rating >= 1; $rating--): ?><option value="<?= $rating ?>"><?= $rating ?> sao</option><?php endfor; ?></select></label>
                    <label>Nhận xét<textarea name="content" rows="4" minlength="5" maxlength="2000" required placeholder="Chia sẻ cảm nhận của bạn về mẫu xe"></textarea></label>
                    <button class="button button-dark" type="submit">Gửi đánh giá</button>
                </form>
            <?php else: ?>
                <p class="review-login">Vui lòng <a href="<?= e(url('/dang-nhap')) ?>">đăng nhập</a> để gửi đánh giá.</p>
            <?php endif; ?>
            <div class="review-list">
                <?php if ($reviews): foreach ($reviews as $review): ?>
                    <article class="review-item"><div><strong><?= e($review['name']) ?></strong><span><?= str_repeat('★', (int) $review['rating']) ?><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></div>
                        <small><?= e(date('d/m/Y', strtotime($review['created_at']))) ?></small><p><?= nl2br(e($review['content'])) ?></p>
                    </article>
                <?php endforeach; else: ?><p class="review-login">Chưa có đánh giá. Hãy là người đầu tiên chia sẻ trải nghiệm.</p><?php endif; ?>
            </div>
        </section>

        <?php if (!empty($relatedCars)): ?>
            <section class="related-cars">
                <div class="section-heading"><div><span class="eyebrow"><span></span> GỢI Ý DÀNH CHO BẠN</span><h2>Xe cùng hãng hoặc phân khúc</h2></div><a class="text-link" href="<?= e(url('/xe')) ?>">Xem tất cả xe ↗</a></div>
                <div class="car-grid">
                    <?php $detailCar = $car; foreach ($relatedCars as $car): require dirname(__DIR__) . '/partials/car-card.php'; endforeach; $car = $detailCar; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
