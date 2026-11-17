<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';
$title = 'Xe yêu thích';
ob_start();
?>
<section class="catalog-hero favorites-hero"><div class="container"><div class="eyebrow"><span></span> DANH SÁCH YÊU THÍCH</div><h1>Những chiếc xe <em>đã được lưu.</em></h1><p>Bạn có thể xem lại và bỏ yêu thích bất cứ lúc nào.</p></div></section>
<section class="section catalog"><div class="container">
    <?php if ($cars): ?>
        <div class="results-line"><span><strong><?= count($cars) ?></strong> chiếc xe trong danh sách yêu thích</span><span>Đã lưu trong phiên làm việc của bạn</span></div>
        <div class="car-grid"><?php foreach ($cars as $car): require dirname(__DIR__) . '/partials/car-card.php'; endforeach; ?></div>
    <?php else: ?>
        <div class="empty-state page-empty">
            <span>♥</span>
            <h2>Chưa có xe yêu thích nào</h2>
            <p>Nhấn vào biểu tượng trái tim trên từng mẫu xe để lưu lại cho lần xem sau.</p>
            <a class="button button-dark" href="<?= e(url('/xe')) ?>">Khám phá xe ngay</a>
        </div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php';
