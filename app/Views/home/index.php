<?php
declare(strict_types=1);
$title = 'Tìm chiếc xe dành riêng cho bạn';
$brands = $brands ?? [];
$bodyTypes = $bodyTypes ?? [];
ob_start(); ?>
<section class="hero">
    <div class="hero-slides" data-hero-carousel aria-label="Ảnh xe nổi bật">
        <?php foreach (($bannerCars ?? []) as $index => $bannerCar): ?>
            <div class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>" aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                <img src="<?= e(image_src($bannerCar['image_url'])) ?>" alt="<?= e($bannerCar['name']) ?>" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
            </div>
        <?php endforeach; ?>
        <?php if (empty($bannerCars)): ?>
            <div class="hero-slide is-active" aria-hidden="false"><img src="https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=2200&q=88" alt="Xe tại Tu Tru Auto"></div>
        <?php endif; ?>
    </div>
    <div class="hero-photo"></div>
    <div class="container hero-content">
        <div class="eyebrow light"><span></span> TU TRU AUTO · SHOWROOM XE CAO CẤP</div>
        <h1>Mỗi hành trình<br>bắt đầu từ <em>một lựa chọn.</em></h1>
        <p>Khám phá những mẫu xe được tuyển chọn kỹ lưỡng,<br>với trải nghiệm mua xe an tâm và minh bạch.</p>
        <div class="hero-actions"><a class="button button-accent" href="<?= e(url('/xe')) ?>">Khám phá bộ sưu tập
                <span>↗</span></a><a class="text-link light-link" href="tel:19006868">☎ &nbsp;1900 6868</a></div>
        <div class="hero-trust"><span>✦ Xe kiểm định chất lượng</span><span>✦ Hỗ trợ trả góp linh hoạt</span><span>✦ Đặc
                quyền hậu mãi</span></div>
    </div>
    <div class="hero-caption">THE ART OF DRIVING · EST. 2012</div>
    <?php if (count($bannerCars ?? []) > 1): ?>
        <div class="hero-carousel-controls" aria-label="Điều khiển banner">
            <button type="button" data-hero-prev aria-label="Ảnh trước">←</button>
            <div><?php foreach ($bannerCars as $index => $bannerCar): ?><button type="button" data-hero-dot="<?= $index ?>" aria-label="Ảnh <?= $index + 1 ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button><?php endforeach; ?></div>
            <button type="button" data-hero-next aria-label="Ảnh tiếp theo">→</button>
        </div>
    <?php endif; ?>
</section>
<?php if (!empty($success)): ?><div class="container"><div class="profile-success home-success" role="status"><?= e($success) ?></div></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="container"><div class="auth-alert" role="alert"><?= e($error) ?></div></div><?php endif; ?>
<section class="quick-search">
    <div class="container search-panel">
        <div class="search-label"><span class="search-icon">⌕</span>
            <div><strong>Tìm chiếc xe phù hợp</strong><small>Lựa chọn theo nhu cầu của bạn</small></div>
        </div>
        <form action="<?= e(url('/xe')) ?>" method="get" class="search-fields"><label><span>Hãng xe</span><select
                    name="brand">
                    <option value="">Tất cả thương hiệu</option>
                        <?php foreach ($brands as $brand): ?><option value="<?= e($brand) ?>"><?= e($brand) ?></option><?php endforeach; ?>
                </select></label><label><span>Kiểu dáng</span><select name="body_type">
                    <option value="">Tất cả kiểu xe</option>
                        <?php foreach ($bodyTypes as $bodyType): ?><option value="<?= e($bodyType) ?>"><?= e($bodyType) ?></option><?php endforeach; ?>
                </select></label><button class="button button-dark">Tìm xe <span>↗</span></button></form>
    </div>
</section>
<section class="section featured" id="xe-noi-bat">
    <div class="container">
        <div class="section-heading">
            <div>
                <div class="eyebrow"><span></span> TUYỂN CHỌN ĐẶC BIỆT</div>
                <h2>Những mẫu xe <em>được yêu thích</em></h2>
                <p>Chất lượng đã được kiểm định, sẵn sàng cùng bạn lăn bánh.</p>
            </div><a class="outline-link" href="<?= e(url('/xe')) ?>">Xem tất cả xe <span>↗</span></a>
        </div>
        <div class="car-grid">
            <?php foreach ($cars as $car): require dirname(__DIR__) . '/partials/car-card.php'; endforeach; ?></div>
    </div>
</section>
<section class="promise">
    <div class="container promise-inner">
        <div>
            <div class="eyebrow light"><span></span> TU TRU DIFFERENCE</div>
            <h2>Mua xe an tâm.<br><em>Trọn vẹn niềm vui.</em></h2>
        </div>
        <div class="promise-cards">
            <article><b>01</b>
                <h3>Minh bạch từ đầu</h3>
                <p>Giá bán rõ ràng, hồ sơ xe đầy đủ. Bạn luôn biết chính xác mình đang chọn điều gì.</p>
            </article>
            <article><b>02</b>
                <h3>Chăm sóc tận tâm</h3>
                <p>Một chuyên viên đồng hành xuyên suốt, từ lúc chọn xe đến sau ngày nhận xe.</p>
            </article>
            <article><b>03</b>
                <h3>Đặc quyền xứng tầm</h3>
                <p>Hỗ trợ tài chính, giao xe tận nơi và những dịch vụ dành riêng cho chủ xe.</p>
            </article>
        </div>
    </div>
</section>
<section class="cta-section">
    <div class="container cta-box">
        <div>
            <div class="eyebrow light"><span></span> SẴN SÀNG KHỞI HÀNH?</div>
            <h2>Chiếc xe tiếp theo<br>đang chờ bạn.</h2>
        </div><a class="button button-accent" href="<?= e(url('/tu-van')) ?>">Đặt lịch tư vấn <span>↗</span></a>
        <div class="cta-orbit"></div>
    </div>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php';