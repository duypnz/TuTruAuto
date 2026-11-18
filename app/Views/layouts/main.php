<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'Showroom ô tô chính hãng';
$cartItems = is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_map(
    static fn ($item): int => is_array($item) ? max(0, (int) ($item['quantity'] ?? 0)) : 0,
    $cartItems
));
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?> | Tu Tru Auto</title>

    <meta name="description"
        content="Khám phá xe mới, xe đã qua sử dụng chất lượng tại Tu Tru Auto. Tư vấn tận tâm, giá minh bạch, lái thử miễn phí.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

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

            <a class="cart-nav-link" href="<?= e(url('/gio-hang')) ?>" aria-label="Giỏ hàng, <?= e($cartCount) ?> xe">
                Giỏ hàng <span><?= e($cartCount) ?></span>
            </a>

            <?php if (isset($_SESSION['user'])): ?>

            <div class="account-nav">
                <div class="account-menu">

                    <button type="button" class="account-trigger" aria-label="Tài khoản người dùng"
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

                        <a href="<?= e(url('/tai-khoan/don-hang')) ?>">
                            Đơn mua xe & đặt cọc
                        </a>

                        <a href="<?= e(url('/yeu-thich')) ?>">
                            Danh sách yêu thích (<?= e(favoriteCount()) ?>)
                        </a>

                        <?php endif; ?>

                        <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>

                        <a href="<?= e(url('/admin')) ?>">
                            Quản trị
                        </a>
                        <a href="<?= e(url('/admin/don-hang')) ?>">
                            Theo dõi đơn xe
                        </a>

                        <?php endif; ?>

                        <form method="post" action="<?= e(url('/dang-xuat')) ?>">

                            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">

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

    <footer class="footer" id="ve-chung-toi" itemscope itemtype="https://schema.org/AutoDealer">
        <div class="container footer-grid">
            <div class="footer-about">
                <a class="brand footer-brand" href="<?= e(url('/')) ?>" aria-label="Tu Tru Auto - Trang chủ">
                    <span class="brand-mark">T</span>
                    <span itemprop="name">TU TRU <small>AUTO</small></span>
                </a>
                <p>Chạm đến chiếc xe mơ ước theo cách dễ dàng hơn. Tận tâm đồng hành trên mọi hành trình.</p>
                <a class="footer-cta" href="tel:19006868">Gọi tư vấn miễn phí <span aria-hidden="true">↗</span></a>

                <div class="footer-social" aria-label="Mạng xã hội">
                    <a href="#" target="_blank" rel="noopener" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path
                                d="M13.5 21v-7.5h2.5l.5-3h-3V8.6c0-.9.3-1.5 1.600-1.500H16.700V4.400C16.400 4.400 15.500 4.300 14.500 4.300c-2.200 0-3.700 1.300-3.700 3.800v2.400H8.300v3h2.500V21h2.700z" />
                        </svg>
                    </a>
                    <a href="#" target="_blank" rel="noopener" aria-label="YouTube">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path
                                d="M21.600 7.200a2.500 2.500 0 0 0-1.800-1.800C18.200 5 12 5 12 5s-6.200 0-7.800.4A2.500 2.500 0 0 0 2.400 7.200C2 8.800 2 12 2 12s0 3.200.4 4.800a2.500 2.500 0 0 0 1.800 1.800C5.800 19 12 19 12 19s6.200 0 7.800-.4a2.500 2.500 0 0 0 1.800-1.800c.4-1.600.4-4.800.4-4.800s0-3.200-.4-4.800zM10 15V9l5.200 3-5.200 3z" />
                        </svg>
                    </a>
                    <a href="#" target="_blank" rel="noopener" aria-label="TikTok">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path
                                d="M16.600 3c.3 2.300 1.600 3.700 3.900 3.900v3c-1.400.1-2.600-.3-3.900-1.100v5.600c0 7.100-7.700 9.300-10.800 4.200-2-3.300-.8-9 5.500-9.200v3.200c-.5.100-1 .2-1.400.4-1.300.4-2 1.300-1.800 2.600.4 2.700 5.300 3.500 4.900-1.800V3h3.600z" />
                        </svg>
                    </a>
                </div>
            </div>

            <nav class="footer-links" aria-label="Khám phá">
                <h4>Khám phá</h4>
                <a href="<?= e(url('/xe')) ?>">Tất cả xe</a>
                <a href="<?= e(url('/xe?body_type=SUV')) ?>">Xe SUV</a>
                <a href="<?= e(url('/xe?fuel=Hybrid')) ?>">Xe Sedan</a>
                <a href="<?= e(url('/xe?fuel=Hybrid')) ?>">Xe Mvp</a>
                <a href="<?= e(url('/xe?fuel=Hybrid')) ?>">Xe Coupe</a>
            </nav>

            <nav class="footer-links" id="dich-vu" aria-label="Dịch vụ">
                <h4>Dịch vụ</h4>
                <a href="tel:19006868">Tư vấn mua xe</a>
                <a href="tel:19006868">Đăng ký lái thử</a>
                <a href="tel:19006868">Thu mua xe cũ</a>
            </nav>

            <div class="footer-contact">
                <h4>Liên hệ</h4>
                <ul class="footer-info">
                    <li>
                        <span class="footer-label">Hotline tư vấn</span>
                        <a class="footer-phone" href="tel:19006868" itemprop="telephone">1900 6868</a>
                    </li>
                    <li>
                        <span class="footer-label">Email</span>
                        <a href="mailto:lienhe@tutruauto.vn" itemprop="email">lienhe@tutruauto.vn</a>
                    </li>
                    <li>
                        <span class="footer-label">Showroom</span>
                        <address itemprop="address">Trịnh Văn Bô, Nam Từ Liêm, Hà Nội, Việt Nam</address>
                    </li>
                    <li>
                        <span class="footer-label">Giờ làm việc</span>
                        <span>Thứ 2 – Chủ nhật: 8:00 – 18:00</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container copyright">
                <span>© <?= date('Y') ?> Tu Tru Auto. Bảo lưu mọi quyền.</span>
                <nav class="footer-legal" aria-label="Chính sách">
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Điều khoản sử dụng</a>
                    <a href="#">Chính sách bảo hành</a>
                </nav>
            </div>
        </div>
    </footer>

    <script
        src="<?= e(url('/public/assets/js/app.js')) ?>?v=<?= e((string) filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js')) ?>"
        defer>
    </script>

</body>

</html>