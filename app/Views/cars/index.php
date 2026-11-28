<?php
declare(strict_types=1);
$title = 'Bộ sưu tập xe';
$brands = $brands ?? [];
$bodyTypes = $bodyTypes ?? [];
$paginationFilters = array_filter($filters, static fn (string $value): bool => $value !== '');
ob_start(); ?>
<section class="catalog-hero"><div class="container"><div class="eyebrow"><span></span> BỘ SƯU TẬP TU TRU</div><h1>Tìm chiếc xe <em>hợp gu bạn.</em></h1><p>Những lựa chọn đáng tin cậy cho mọi hành trình.</p></div></section>
<section class="section catalog"><div class="container">
    <form class="filters" method="get" action="<?= e(url('/xe')) ?>">
        <label class="keyword"><span>⌕</span><input name="q" placeholder="Tìm theo tên xe, thương hiệu..." value="<?= e($filters['keyword']) ?>"></label>
        <select name="brand"><option value="">Tất cả thương hiệu</option><?php foreach ($brands as $brand): ?><option value="<?= e($brand) ?>" <?= $filters['brand'] === $brand ? 'selected' : '' ?>><?= e($brand) ?></option><?php endforeach; ?></select>
        <select name="body_type"><option value="">Kiểu dáng</option><?php foreach ($bodyTypes as $type): ?><option value="<?= e($type) ?>" <?= $filters['body_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select>
        <select name="fuel"><option value="">Nhiên liệu</option><?php foreach (['Xăng', 'Hybrid', 'Điện', 'Dầu'] as $fuel): ?><option value="<?= e($fuel) ?>" <?= $filters['fuel'] === $fuel ? 'selected' : '' ?>><?= e($fuel) ?></option><?php endforeach; ?></select>
        <select name="sort"><option value="">Mới nhất</option><option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option><option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option></select>
        <button class="button button-dark">Lọc xe</button>
    </form>
    <div class="results-line"><span><strong><?= $totalCars ?></strong> mẫu xe phù hợp</span><span>Giá niêm yết, đã gồm VAT</span></div>
    <?php if ($cars): ?>
        <div class="car-grid"><?php foreach ($cars as $car): require dirname(__DIR__) . '/partials/car-card.php'; endforeach; ?></div>
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Phân trang danh sách xe">
                <?php if ($currentPage > 1): ?>
                    <?php $previousQuery = http_build_query(array_merge($paginationFilters, ['page' => $currentPage - 1])); ?>
                    <a class="pagination-link" href="<?= e(url('/xe') . '?' . $previousQuery) ?>" aria-label="Trang trước">‹</a>
                <?php endif; ?>
                <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                    <?php if ($pageNumber === $currentPage): ?>
                        <span class="pagination-link is-current" aria-current="page"><?= $pageNumber ?></span>
                    <?php else: ?>
                        <?php $pageQuery = http_build_query(array_merge($paginationFilters, ['page' => $pageNumber])); ?>
                        <a class="pagination-link" href="<?= e(url('/xe') . '?' . $pageQuery) ?>" aria-label="Trang <?= $pageNumber ?>"><?= $pageNumber ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <?php $nextQuery = http_build_query(array_merge($paginationFilters, ['page' => $currentPage + 1])); ?>
                    <a class="pagination-link" href="<?= e(url('/xe') . '?' . $nextQuery) ?>" aria-label="Trang tiếp theo">›</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state"><span>⌕</span><h2>Chưa tìm thấy mẫu xe phù hợp</h2><p>Thử thay đổi bộ lọc hoặc liên hệ để chúng tôi tìm xe giúp bạn.</p><a class="button button-dark" href="<?= e(url('/xe')) ?>">Xem tất cả xe</a></div>
    <?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php';
