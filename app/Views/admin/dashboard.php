<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Helpers/view.php';

$monthlyStats = $monthlyStats ?? [];
$salesStats = $salesStats ?? ['revenue' => 0, 'order_count' => 0, 'sold_cars' => 0];
$showEmailTest = $showEmailTest ?? false;
$maxRevenue = max(1, ...array_map(static fn (array $month): int => (int) $month['revenue'], $monthlyStats));
$maxOrders = max(1, ...array_map(static fn (array $month): int => (int) $month['order_count'], $monthlyStats));
$plotLeft = 64;
$plotTop = 20;
$plotWidth = 900;
$plotHeight = 220;
$pointStep = count($monthlyStats) > 1 ? $plotWidth / (count($monthlyStats) - 1) : 0;
$points = [];
foreach ($monthlyStats as $index => $month) {
    $x = $plotLeft + $index * $pointStep;
    $y = $plotTop + $plotHeight - ((int) $month['order_count'] / $maxOrders) * $plotHeight;
    $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
}
$revenueScale = $maxRevenue >= 1000000000 ? 1000000000 : ($maxRevenue >= 1000000 ? 1000000 : 1);
$revenueUnit = $revenueScale === 1000000000 ? 'tỷ' : ($revenueScale === 1000000 ? 'triệu' : '₫');
ob_start();
?>
<section class="admin-page admin-dashboard">
    <div class="container">
        <div class="admin-heading dashboard-heading">
            <div>
                <span class="eyebrow"><span></span> TRUNG TÂM ĐIỀU HÀNH</span>
                <h1>Xin chào, <?= e($admin['name']) ?></h1>
                <p>Xin chào <strong><?= e($admin['email']) ?></strong>. Theo dõi hoạt động kinh doanh và lịch đặt cọc tại đây.</p>
            </div>
            <span class="admin-badge">ADMIN</span>
        </div>

        <div class="admin-stats dashboard-overview">
            <article><span>Tổng tài khoản</span><strong><?= e($userCount) ?></strong><small>Khách hàng và quản trị viên</small></article>
            <article><span>Xe đang quản lý</span><strong><?= e($carCount) ?></strong><small>Tin xe trong hệ thống</small></article>
            <article><span>Đơn chưa hủy</span><strong><?= e($salesStats['order_count'] ?? 0) ?></strong><small>Đơn đặt cọc và lịch sử bán</small></article>
        </div>

        <section class="dashboard-panel chart-panel">
            <div class="dashboard-panel-heading">
                <div><span class="eyebrow"><span></span> HIỆU QUẢ KINH DOANH</span><h2>Doanh thu & đơn hàng</h2>
                    <p>Diễn biến 12 tháng gần nhất · doanh thu tính từ đơn đã xác nhận mua.</p></div>
                <a class="button button-outline" href="<?= e(url('/admin/don-hang')) ?>">Xem danh sách đơn</a>
            </div>
            <div class="admin-stats admin-sales-stats">
                <article class="stat-revenue"><span>Doanh thu đã xác nhận</span><strong><?= money($salesStats['revenue'] ?? 0) ?></strong><small>Không bao gồm đơn đang đặt cọc</small></article>
                <article><span>Đơn hàng hoạt động</span><strong><?= e($salesStats['order_count'] ?? 0) ?></strong><small>Đã loại trừ đơn bị hủy</small></article>
                <article><span>Xe đã bán</span><strong><?= e($salesStats['sold_cars'] ?? 0) ?></strong><small>Từ các đơn đã xác nhận mua</small></article>
            </div>
            <div class="sales-chart-legend">
                <span><i class="legend-revenue"></i> Doanh thu (<?= e($revenueUnit) ?>)</span>
                <span><i class="legend-orders"></i> Số đơn hàng</span>
            </div>
            <div class="sales-chart-wrap">
                <svg class="sales-chart" viewBox="0 0 1000 285" role="img" aria-labelledby="sales-chart-title sales-chart-description">
                    <title id="sales-chart-title">Biểu đồ doanh thu và số đơn hàng theo tháng</title>
                    <desc id="sales-chart-description">Cột thể hiện doanh thu đã xác nhận mua; đường thể hiện số đơn hàng không bị hủy trong 12 tháng gần nhất.</desc>
                    <?php for ($grid = 0; $grid <= 4; $grid++): ?>
                        <?php $gridY = $plotTop + ($plotHeight / 4) * $grid; $gridValue = $maxRevenue * (1 - $grid / 4); ?>
                        <line x1="<?= $plotLeft ?>" y1="<?= number_format($gridY, 2, '.', '') ?>" x2="<?= $plotLeft + $plotWidth ?>" y2="<?= number_format($gridY, 2, '.', '') ?>" class="chart-grid-line"/>
                        <text x="54" y="<?= number_format($gridY + 4, 2, '.', '') ?>" text-anchor="end" class="chart-axis-label"><?= e(number_format($gridValue / $revenueScale, 0, ',', '.')) ?></text>
                    <?php endfor; ?>
                    <?php foreach ($monthlyStats as $index => $month): ?>
                        <?php
                        $x = $plotLeft + $index * $pointStep;
                        $barWidth = min(36, max(12, $plotWidth / max(1, count($monthlyStats)) * .42));
                        $barHeight = ((int) $month['revenue'] / $maxRevenue) * $plotHeight;
                        $barY = $plotTop + $plotHeight - $barHeight;
                        ?>
                        <rect x="<?= number_format($x - $barWidth / 2, 2, '.', '') ?>" y="<?= number_format($barY, 2, '.', '') ?>"
                              width="<?= number_format($barWidth, 2, '.', '') ?>" height="<?= number_format(max(1, $barHeight), 2, '.', '') ?>"
                              rx="5" class="chart-revenue-bar">
                            <title><?= e($month['label']) ?>: <?= e(money($month['revenue'])) ?> · <?= e($month['order_count']) ?> đơn</title>
                        </rect>
                        <text x="<?= number_format($x, 2, '.', '') ?>" y="268" text-anchor="middle" class="chart-month-label"><?= e($month['label']) ?></text>
                    <?php endforeach; ?>
                    <?php if ($points): ?><polyline points="<?= e(implode(' ', $points)) ?>" class="chart-order-line"/><?php endif; ?>
                    <?php foreach ($monthlyStats as $index => $month): ?>
                        <?php $x = $plotLeft + $index * $pointStep; $y = $plotTop + $plotHeight - ((int) $month['order_count'] / $maxOrders) * $plotHeight; ?>
                        <circle cx="<?= number_format($x, 2, '.', '') ?>" cy="<?= number_format($y, 2, '.', '') ?>" r="4" class="chart-order-point">
                            <title><?= e($month['label']) ?>: <?= e($month['order_count']) ?> đơn</title>
                        </circle>
                    <?php endforeach; ?>
                    <text x="972" y="24" text-anchor="end" class="chart-axis-label">Tối đa <?= e($maxOrders) ?> đơn</text>
                </svg>
            </div>
        </section>

        <div class="dashboard-quick-links">
            <a class="dashboard-action-card" href="<?= e(url('/admin/don-hang')) ?>"><span class="dashboard-action-icon">↗</span><span><strong>Quản lý đơn đặt cọc</strong><small>Tìm kiếm đơn, cập nhật tiền cọc và lịch nhận xe</small></span><b>→</b></a>
            <a class="dashboard-action-card" href="<?= e(url('/admin/xe')) ?>"><span class="dashboard-action-icon">▣</span><span><strong>Quản lý kho xe</strong><small>Cập nhật thông tin, giá và tình trạng xe</small></span><b>→</b></a>
            <?php if ($showEmailTest): ?><a class="dashboard-action-card" href="<?= e(url('/admin/email-test')) ?>"><span class="dashboard-action-icon">✉</span><span><strong>Kiểm thử Gmail SMTP</strong><small>Gửi email thử trong môi trường phát triển</small></span><b>→</b></a><?php endif; ?>
        </div>

        <div class="admin-panel"><h2>Tài khoản quản trị</h2><p>Quyền quản trị được cấp riêng trong cơ sở dữ liệu. Người dùng đăng ký mới luôn chỉ có quyền khách hàng.</p><a class="button button-dark" href="<?= e(url('/')) ?>">Về trang chủ <span>↗</span></a></div>
    </div>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php'; ?>
