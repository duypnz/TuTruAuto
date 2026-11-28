<?php
declare(strict_types=1);
$title = 'Về chúng tôi';
ob_start();
?>
<section class="about-hero">
    <?php $heroImage = $featuredCars[0]['image_url'] ?? 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=2200&q=88'; ?>
    <img src="<?= e(image_src($heroImage)) ?>"
        alt="Dòng xe đang trưng bày tại Tu Tru Auto" fetchpriority="high">
    <div class="about-hero-overlay"></div>
    <div class="container about-hero-content">
        <div class="eyebrow light"><span></span> TU TRU AUTO</div>
        <h1>Giới thiệu về<br><em>Tu Tru Auto</em></h1>
        <p>Chúng tôi kết nối khách hàng với những chiếc xe phù hợp<br>qua trải nghiệm mua xe minh bạch và tận tâm.</p>
    </div>
</section>

<section class="about-intro section">
    <div class="container about-intro-grid">
        <div class="about-intro-image">
            <?php $introImage = $featuredCars[1]['image_url'] ?? $heroImage; ?>
            <img src="<?= e(image_src($introImage)) ?>"
                alt="Xe tại Tu Tru Auto" loading="lazy">
        </div>
        <div class="about-intro-copy">
            <div class="eyebrow"><span></span> Câu chuyện của chúng tôi</div>
            <h2>Đồng hành trên<br><em>mọi hành trình.</em></h2>
            <p>Tu Tru Auto là showroom ô tô đa thương hiệu, nơi khách hàng có thể tìm thấy những mẫu xe được tuyển chọn kỹ lưỡng cùng giải pháp mua xe phù hợp.</p>
            <p>Với triết lý đặt khách hàng làm trọng tâm, chúng tôi chú trọng tư vấn minh bạch, quy trình rõ ràng và dịch vụ chăm sóc tận tình trước – trong – sau khi mua xe.</p>
            <p>Đội ngũ tư vấn của chúng tôi luôn bắt đầu từ nhu cầu thực tế: phong cách sống, ngân sách, nhu cầu di chuyển và kế hoạch sử dụng lâu dài. Từ đó, mỗi chiếc xe được đề xuất đều có lý do rõ ràng.</p>
            <div class="about-signature">
                <strong>Tu Tru Auto</strong>
                <span>Showroom xe chính hãng</span>
            </div>
        </div>
    </div>
</section>

<section class="about-footprint">
    <div class="container">
        <div class="about-footprint-heading">
            <div class="eyebrow"><span></span> BỘ SƯU TẬP ĐA DẠNG</div>
            <h2>Lựa chọn cho mọi hành trình</h2>
            <p>Khám phá những mẫu xe được Tu Tru Auto tuyển chọn cho từng nhu cầu sử dụng, từ di chuyển hàng ngày đến những hành trình dài.</p>
        </div>
        <div class="about-footprint-cars">
            <?php if (!empty($featuredCars)): ?>
            <?php foreach (array_values($featuredCars) as $index => $featuredCar): ?>
                <article>
                    <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                    <img src="<?= e(image_src($featuredCar['image_url'])) ?>"
                        alt="<?= e($featuredCar['name']) ?>" loading="lazy">
                    <h3><?= e($featuredCar['name']) ?></h3>
                </article>
            <?php endforeach; ?>
            <?php else: ?>
                <article>
                    <span>01</span>
                    <img src="https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=1200&q=85"
                        alt="Mẫu xe tại Tu Tru Auto" loading="lazy">
                    <h3>Tuyển chọn nổi bật</h3>
                </article>
                <article>
                    <span>02</span>
                    <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=85"
                        alt="Mẫu xe tại Tu Tru Auto" loading="lazy">
                    <h3>Tuyển chọn đa dạng</h3>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="about-vision">
    <?php $visionImage = $featuredCars[2]['image_url'] ?? $heroImage; ?>
    <img src="<?= e(image_src($visionImage)) ?>"
        alt="Dòng xe Tu Tru Auto" loading="lazy">
    <div class="about-vision-overlay"></div>
    <div class="container about-vision-grid">
        <article>
            <h2>Tầm nhìn</h2>
            <p>Trở thành showroom ô tô tin cậy, mang đến lựa chọn xe chất lượng và trải nghiệm mua xe trọn vẹn.</p>
        </article>
        <article>
            <h2>Sứ mệnh</h2>
            <p>Giúp mỗi khách hàng tìm được chiếc xe phù hợp với nhu cầu, ngân sách và hành trình của mình.</p>
        </article>
        <article>
            <h2>Triết lý thương hiệu</h2>
            <p>Đặt khách hàng làm trọng tâm, không ngừng sáng tạo để mang đến sản phẩm đẳng cấp và trải nghiệm xuất sắc.</p>
        </article>
        <article>
            <h2>Giá trị cốt lõi</h2>
            <p>Sản phẩm đẳng cấp, giá tốt và chính sách hậu mãi vượt trội.</p>
        </article>
    </div>
</section>

<section class="about-core section">
    <div class="container">
        <div class="section-heading">
            <div>
                <div class="eyebrow"><span></span> Điều chúng tôi theo đuổi</div>
                <h2>Giá trị <em>cốt lõi</em></h2>
            </div>
        </div>
        <div class="about-core-grid">
            <article>
                <span class="about-core-number">01</span>
                <h3>Sản phẩm chất lượng</h3>
                <p>Tuyển chọn những dòng xe đáp ứng yêu cầu về chất lượng, tiện nghi và độ an toàn.</p>
            </article>
            <article>
                <span class="about-core-number">02</span>
                <h3>Giá trị minh bạch</h3>
                <p>Mọi thông tin giá bán, phiên bản và ưu đãi đều được tư vấn rõ ràng, dễ hiểu.</p>
            </article>
            <article>
                <span class="about-core-number">03</span>
                <h3>Dịch vụ tận tâm</h3>
                <p>Đồng hành cùng khách hàng từ lúc tìm xe đến sau ngày nhận xe.</p>
            </article>
        </div>
    </div>
</section>

<section class="about-experience">
    <div class="container about-experience-grid">
        <div>
            <div class="eyebrow light"><span></span> TRẢI NGHIỆM TU TRU</div>
            <h2>Một hành trình mua xe<br><em>được chăm chút.</em></h2>
        </div>
        <div class="about-experience-steps">
            <article><b>01</b><h3>Tìm hiểu nhu cầu</h3><p>Tư vấn theo phong cách sống, ngân sách và mục đích sử dụng.</p></article>
            <article><b>02</b><h3>Tham quan & lái thử</h3><p>Trải nghiệm trực tiếp từng mẫu xe trước khi đưa ra quyết định.</p></article>
            <article><b>03</b><h3>Hỗ trợ hoàn tất</h3><p>Hỗ trợ thủ tục, tài chính và giao xe đúng kế hoạch đã thống nhất.</p></article>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container cta-box">
        <div>
            <div class="eyebrow light"><span></span> BẮT ĐẦU HÀNH TRÌNH</div>
            <h2>Tìm chiếc xe<br>phù hợp với bạn.</h2>
        </div>
        <a class="button button-accent" href="<?= e(url('/xe')) ?>">Khám phá bộ sưu tập <span>↗</span></a>
        <div class="cta-orbit"></div>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
