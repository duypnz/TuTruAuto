<?php
declare(strict_types=1);
$title = 'Không tìm thấy xe';
ob_start(); ?><section class="empty-state page-empty"><span>404</span><h1>Không tìm thấy trang này</h1><p>Mẫu xe có thể đã được cập nhật hoặc không còn trong bộ sưu tập.</p><a class="button button-dark" href="<?= e(url('/xe')) ?>">Khám phá các mẫu xe</a></section><?php $content = ob_get_clean(); require dirname(__DIR__) . '/layouts/main.php';
