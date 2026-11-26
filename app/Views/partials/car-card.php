
<article class="car-card">
    <div class="car-image-wrap">
        <a class="car-image" href="<?= e(url('/xe/' . $car['slug'])) ?>">
            <img src="<?= e(image_src($car['image_url'])) ?>"
                 alt="<?= e($car['name']) ?>"
                 loading="lazy">
            <span class="image-badge">
                <?= e($car['badge'] ?: 'Sẵn sàng giao') ?>
            </span>
        </a>
    </div>

    <div class="car-info">
        <div class="car-brand">
            <?= e($car['brand']) ?>
            <span>· <?= e($car['year']) ?></span>
        </div>

        <h3>
            <a href="<?= e(url('/xe/' . $car['slug'])) ?>">
                <?= e($car['name']) ?>
            </a>
        </h3>

        <div class="specs">
            <span>⚙ <?= e($car['transmission']) ?></span>
            <span>◉ <?= e($car['fuel']) ?></span>
            <span>↗ <?= e($car['body_type']) ?></span>
        </div>
        <p class="car-stock <?= $car['stock_quantity'] !== null && (int) $car['stock_quantity'] > 0 ? 'is-available' : ($car['stock_quantity'] === null ? '' : 'is-preorder') ?>">
            <?= $car['stock_quantity'] === null
                ? 'Liên hệ showroom để xác nhận tồn kho'
                : ((int) $car['stock_quantity'] > 0
                    ? 'Còn ' . (int) $car['stock_quantity'] . ' xe tại cửa hàng'
                    : 'Nhận đặt cọc · dự kiến ' . (int) ($car['lead_time_days'] ?? 21) . ' ngày') ?>
        </p>

        <div class="car-bottom">
            <strong><?= e(car_price_label($car)) ?></strong>

            <div class="car-bottom-actions">
                <?php if (isCustomerLoggedIn()): ?>
                    <form method="post"
                          action="<?= e(url('/yeu-thich/' . $car['slug'] . '/toggle')) ?>"
                          class="favorite-form">

                        <input type="hidden"
                               name="_csrf"
                               value="<?= e(\App\Core\Csrf::token()) ?>">

                        <button type="submit"
                                class="favorite-toggle <?= isFavorite($car['slug']) ? 'is-active' : '' ?>"
                                aria-label="<?= isFavorite($car['slug']) ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>"
                                title="<?= isFavorite($car['slug']) ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>">
                            <span aria-hidden="true">
                                <?= isFavorite($car['slug']) ? '♥' : '♡' ?>
                            </span>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="favorite-toggle"
                       href="<?= e(url('/dang-nhap')) ?>"
                       aria-label="Đăng nhập để thêm xe yêu thích"
                       title="Đăng nhập để thêm xe yêu thích">
                        <span aria-hidden="true">♡</span>
                    </a>
                <?php endif; ?>

                <a class="arrow-link"
                   href="<?= e(url('/xe/' . $car['slug'])) ?>"
                   aria-label="Xem <?= e($car['name']) ?>">
                    ↗
                </a>
            </div>
        </div>
    </div>
</article>
