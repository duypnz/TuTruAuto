<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$title = $title ?? 'Showroom ô tô chính hãng';
$layoutPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$layoutBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($layoutBase !== '' && $layoutBase !== '/' && str_starts_with($layoutPath, $layoutBase)) {
    $layoutPath = substr($layoutPath, strlen($layoutBase)) ?: '/';
}
$isAdminArea = str_starts_with($layoutPath, '/admin')
    && (($_SESSION['user']['role'] ?? '') === 'admin');
$adminNavigation = [
    ['path' => '/admin', 'prefix' => false, 'label' => 'Tổng quan', 'icon' => '⌂'],
    ['path' => '/admin/xe', 'prefix' => true, 'label' => 'Quản lý xe', 'icon' => '▣'],
    ['path' => '/admin/danh-muc', 'prefix' => true, 'label' => 'Hãng & kiểu dáng', 'icon' => '◈'],
    ['path' => '/admin/don-hang', 'prefix' => true, 'label' => 'Đơn đặt cọc', 'icon' => '▤'],
    ['path' => '/admin/voucher', 'prefix' => true, 'label' => 'Mã giảm giá', 'icon' => '％'],
    ['path' => '/admin/lien-he', 'prefix' => true, 'label' => 'Liên hệ khách hàng', 'icon' => '✉'],
    ['path' => '/admin/chat', 'prefix' => true, 'label' => 'Chat hỗ trợ', 'icon' => '◌'],
    ['path' => '/admin/tai-khoan', 'prefix' => true, 'label' => 'Tài khoản', 'icon' => '♙'],
    ['path' => '/admin/thanh-toan', 'prefix' => true, 'label' => 'Thanh toán', 'icon' => '₫'],
];
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?> | Tu Tru Auto</title>

    <meta name="description"
        content="Khám phá các dòng xe mới chính hãng tại Tu Tru Auto. Tư vấn tận tâm, giá minh bạch, lái thử miễn phí.">

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
                <a class="<?= $layoutPath === '/' ? 'is-active' : '' ?>" href="<?= e(url('/')) ?>">Trang chủ</a>
                <a class="<?= str_starts_with($layoutPath, '/xe') || str_starts_with($layoutPath, '/so-sanh') ? 'is-active' : '' ?>" href="<?= e(url('/xe')) ?>">Mua xe</a>
                <a href="<?= e(url('/#dich-vu')) ?>">Dịch vụ</a>
                <a class="<?= $layoutPath === '/ve-chung-toi' ? 'is-active' : '' ?>" href="<?= e(url('/ve-chung-toi')) ?>">Về chúng tôi</a>
            </nav>

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
                            Đơn đặt cọc của tôi
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
        <?php if ($isAdminArea): ?>
            <div class="admin-shell">
                <aside class="admin-sidebar" aria-label="Điều hướng quản trị">
                    <div class="admin-sidebar-brand"><span class="admin-sidebar-mark">T</span><div><strong>SHOWROOM</strong><small>QUẢN TRỊ HỆ THỐNG</small></div></div>
                    <nav>
                        <?php foreach ($adminNavigation as $item): ?>
                            <?php $active = $item['prefix']
                                ? str_starts_with($layoutPath, $item['path'])
                                : $layoutPath === $item['path']; ?>
                            <a href="<?= e(url($item['path'])) ?>" class="<?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>>
                                <span aria-hidden="true"><?= e($item['icon']) ?></span><?= e($item['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <a class="admin-sidebar-home" href="<?= e(url('/')) ?>">← Về website</a>
                </aside>
                <div class="admin-content"><?= $content ?></div>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
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
                <a href="<?= e(url('/ve-chung-toi')) ?>">Về Tu Tru Auto</a>
                <a href="<?= e(url('/xe')) ?>">Tất cả xe</a>
                <a href="<?= e(url('/xe?body_type=SUV')) ?>">Xe SUV</a>
                <a href="<?= e(url('/xe?body_type=Sedan')) ?>">Xe Sedan</a>
                <a href="<?= e(url('/xe?body_type=MPV')) ?>">Xe MPV</a>
                <a href="<?= e(url('/xe?body_type=Coupe')) ?>">Xe Coupe</a>
            </nav>

            <nav class="footer-links" id="dich-vu" aria-label="Dịch vụ">
                <h4>Dịch vụ</h4>
                <a href="tel:19006868">Tư vấn mua xe</a>
                <a href="tel:19006868">Đăng ký lái thử</a>
                <a href="<?= e(url('/tu-van')) ?>">Hỗ trợ tài chính</a>
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
    <?php if (isCustomerLoggedIn()): ?>
        <section class="support-chat" data-support-chat
            data-messages-url="<?= e(url('/tai-khoan/chat/tin-nhan')) ?>"
            data-send-url="<?= e(url('/tai-khoan/chat/tin-nhan')) ?>"
            data-unread-url="<?= e(url('/tai-khoan/chat/chua-doc')) ?>"
            data-csrf="<?= e(\App\Core\Csrf::token()) ?>"
            aria-labelledby="support-chat-title" hidden>
            <header class="support-chat-header" data-chat-drag-handle>
                <div><strong id="support-chat-title">Hỗ trợ khách hàng</strong><small>Nhắn tin trực tiếp với nhân viên</small></div>
                <div class="support-chat-header-actions">
                    <button type="button" class="support-chat-minimize" data-support-chat-minimize aria-label="Thu nhỏ khung chat">−</button>
                    <button type="button" class="support-chat-close" data-support-chat-close aria-label="Đóng cửa sổ chat">×</button>
                </div>
            </header>
            <div class="support-chat-messages" data-chat-messages aria-live="polite">
                <p class="support-chat-state">Đang tải tin nhắn...</p>
            </div>
            <p class="support-chat-error" data-chat-error role="status" hidden></p>
            <form class="support-chat-form" data-chat-form>
                <label class="sr-only" for="support-chat-input">Tin nhắn</label>
                <textarea id="support-chat-input" name="message" rows="2" maxlength="2000" placeholder="Nhập tin nhắn..." required></textarea>
                <button type="submit" aria-label="Gửi tin nhắn">Gửi</button>
            </form>
        </section>
        <button type="button" class="support-chat-bubble" data-support-chat-bubble
            aria-label="Mở chat hỗ trợ" aria-expanded="false">
            <span aria-hidden="true">✉</span>
            <span class="support-chat-bubble-count" data-support-chat-unread aria-live="polite" hidden>0</span>
        </button>
    <?php endif; ?>

    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>
    <script
        src="<?= e(url('/public/assets/js/app.js')) ?>?v=<?= e((string) filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js')) ?>"
        defer>
    </script>

</body>

</html>
