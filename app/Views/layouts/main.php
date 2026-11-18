<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'Showroom ô tô chính hãng';
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?> | Tu Tru Auto</title>

    <meta name="description" content="Khám phá xe mới, xe đã qua sử dụng chất lượng tại Tu Tru Auto. Tư vấn tận tâm, giá minh bạch, lái thử miễn phí.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="<?= e(url('/public/assets/css/style.css')) ?>?v=<?= e((string) filemtime(dirname(__DIR__, 3) . '/public/assets/css/style.css')) ?>">
</head>

<body>

    <div class="topline">
        <div class="container top-inner">
            <span>✦ Tư vấn mua xe tận tâm · Đồng hành trên mọi hành trình</span>
            <span>Hotline <strong>1900 6868</strong></span>
        </div>
    </div>

    <header class="site-header">
        <div class="container nav-wrap">

            <a class="brand" href="<?= e(url('/')) ?>">
                <span class="brand-mark">T</span>
                <span>TU TRU <small>AUTO</small></span>
            </a>

            <nav class="main-nav">
                <a href="<?= e(url('/')) ?>">Trang chủ</a>
                <a href="<?= e(url('/xe')) ?>">Mua xe</a>
                <a href="<?= e(url('/#dich-vu')) ?>">Dịch vụ</a>
                <a href="<?= e(url('/#ve-chung-toi')) ?>">Về chúng tôi</a>
            </nav>

            <?php if (isset($_SESSION['user'])): ?>

                <div class="account-nav">
                    <div class="account-menu">

                        <button
                            type="button"
                            class="account-trigger"
                            aria-label="Tài khoản người dùng"
                            aria-expanded="false">

                            <span class="account-avatar">
                                <?= e(strtoupper(substr(trim((string) ($_SESSION['user']['name'] ?? 'U')), 0, 1))) ?>
                            </span>

                            <span class="account-greeting">
                                Xin chào, <?= e($_SESSION['user']['name'] ?? '') ?>
                            </span>

                            <span class="account-link">Tài khoản</span>
                        </button>

                        <div class="account-dropdown">

                            <div class="account-dropdown-header">
                                Tài khoản của bạn
                            </div>

                            <?php if (isCustomerLoggedIn()): ?>

                                <a href="<?= e(url('/tai-khoan/ho-so')) ?>">
                                    Thông tin hồ sơ
                                </a>

                                <a href="<?= e(url('/yeu-thich')) ?>">
                                    Danh sách yêu thích (<?= e(favoriteCount()) ?>)
                                </a>

                            <?php endif; ?>

                            <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>

                                <a href="<?= e(url('/admin')) ?>">
                                    Quản trị
                                </a>

                            <?php endif; ?>

                            <form method="post" action="<?= e(url('/dang-xuat')) ?>">

                                <input
                                    type="hidden"
                                    name="_csrf"
                                    value="<?= e(\App\Core\Csrf::token()) ?>">

                                <button type="submit">
                                    Đăng xuất
                                </button>

                            </form>

                        </div>
                    </div>
                </div>

            <?php else: ?>

                <a class="nav-cta" href="<?= e(url('/dang-nhap')) ?>">
                    Đăng nhập <span>↗</span>
                </a>

            <?php endif; ?>

            <button class="menu-toggle" aria-label="Mở menu">
                ☰
            </button>

        </div>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer class="footer" id="ve-chung-toi">

        <div class="container footer-grid">

            <div>
                <a class="brand footer-brand" href="<?= e(url('/')) ?>">
                    <span class="brand-mark">T</span>
                    <span>TU TRU <small>AUTO</small></span>
                </a>

                <p>
                    Chạm đến chiếc xe mơ ước<br>
                    theo cách dễ dàng hơn.
                </p>
            </div>

            <div>
                <h4>Khám phá</h4>
                <a href="<?= e(url('/xe')) ?>">Tất cả xe</a>
                <a href="<?= e(url('/xe?body_type=SUV')) ?>">Xe SUV</a>
                <a href="<?= e(url('/xe?fuel=Hybrid')) ?>">Xe Hybrid</a>
            </div>

            <div id="dich-vu">
                <h4>Dịch vụ</h4>
                <a href="tel:19006868">Tư vấn mua xe</a>
                <a href="tel:19006868">Đăng ký lái thử</a>
                <a href="tel:19006868">Thu mua xe cũ</a>
            </div>

            <div>
                <h4>Liên hệ</h4>
                <p>Hotline: <strong>1900 6868</strong></p>
                <p>TP. Hồ Chí Minh, Việt Nam</p>
            </div>

        </div>

        <div class="container copyright">
            © 2026 Tu Tru Auto. Đi cùng bạn trên mọi cung đường.
        </div>

    </footer>

    <script
        src="<?= e(url('/public/assets/js/app.js')) ?>?v=<?= e((string) filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js')) ?>"
        defer>
    </script>

</body>

</html>
