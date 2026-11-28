<?php
declare(strict_types=1);

use App\Core\Router;
use App\Core\Environment;
use App\Controllers\HomeController;
use App\Controllers\CarController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\Admin\CarController as AdminCarController;
use App\Controllers\Admin\CatalogController as AdminCatalogController;
use App\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Controllers\Admin\EmailTestController as AdminEmailTestController;
use App\Controllers\OrderController;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Core/Autoloader.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => isset($_SERVER['HTTPS'])]);
    session_start();
}
$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/xe', [CarController::class, 'index']);
$router->get('/xe/{slug}', [CarController::class, 'show']);
$router->get('/so-sanh', [CarController::class, 'compare']);
$router->post('/xe/{slug}/yeu-cau', [CarController::class, 'submitCarRequest']);
$router->post('/lien-he', [CarController::class, 'submitContact']);
$router->get('/tu-van', [CarController::class, 'adviceForm']);
$router->post('/tu-van', [CarController::class, 'submitAdvice']);
$router->post('/xe/{slug}/danh-gia', [CarController::class, 'submitReview']);
$router->post('/voucher/kiem-tra', [CarController::class, 'checkVoucher']);
$router->get('/tai-khoan/don-hang', [OrderController::class, 'customerOrders']);
$router->get('/tai-khoan/don-hang/{id}', [OrderController::class, 'customerOrderDetail']);
$router->get('/dang-nhap', [AuthController::class, 'loginForm']);
$router->post('/dang-nhap', [AuthController::class, 'login']);
$router->get('/dang-ky', [AuthController::class, 'registerForm']);
$router->post('/dang-ky', [AuthController::class, 'register']);
$router->post('/dang-xuat', [AuthController::class, 'logout']);
$router->get('/tai-khoan/ho-so', [AuthController::class, 'profile']);
$router->post('/tai-khoan/ho-so', [AuthController::class, 'updateProfile']);
$router->get('/admin', [AdminController::class, 'index']);
if (Environment::get('APP_ENV') === 'development') {
    $router->get('/admin/email-test', [AdminEmailTestController::class, 'index']);
    $router->post('/admin/email-test', [AdminEmailTestController::class, 'send']);
}
$router->get('/yeu-thich', [CarController::class, 'favorites']);
$router->post('/yeu-thich/{slug}/toggle', [CarController::class, 'toggleFavorite']);
$router->get('/admin/xe', [AdminCarController::class, 'index']);
$router->get('/admin/don-hang', [OrderController::class, 'adminOrders']);
$router->post('/admin/don-hang/{id}/cap-nhat', [OrderController::class, 'updateOrder']);
$router->get('/admin/voucher', [AdminVoucherController::class, 'index']);
$router->post('/admin/voucher', [AdminVoucherController::class, 'create']);
$router->get('/admin/voucher/{id}/sua', [AdminVoucherController::class, 'editForm']);
$router->post('/admin/voucher/{id}/cap-nhat', [AdminVoucherController::class, 'update']);
$router->post('/admin/voucher/{id}/xoa', [AdminVoucherController::class, 'delete']);
$router->get('/admin/danh-muc', [AdminCatalogController::class, 'index']);
$router->post('/admin/danh-muc', [AdminCatalogController::class, 'create']);
$router->get('/admin/tai-khoan', [AdminUserController::class, 'index']);
$router->post('/admin/tai-khoan', [AdminUserController::class, 'create']);
$router->post('/admin/tai-khoan/{id}/cap-nhat', [AdminUserController::class, 'updateAccess']);
$router->get('/admin/lien-he', [AdminInquiryController::class, 'index']);
$router->post('/admin/lien-he/{id}/cap-nhat', [AdminInquiryController::class, 'updateStatus']);
$router->get('/admin/thanh-toan', [AdminSettingsController::class, 'payment']);
$router->post('/admin/thanh-toan', [AdminSettingsController::class, 'savePayment']);
$router->get('/admin/xe/them', [AdminCarController::class, 'createForm']);
$router->post('/admin/xe', [AdminCarController::class, 'store']);
$router->get('/admin/xe/{id}/sua', [AdminCarController::class, 'editForm']);
$router->post('/admin/xe/{id}/cap-nhat', [AdminCarController::class, 'update']);
$router->post('/admin/xe/{id}/xoa', [AdminCarController::class, 'delete']);
$router->get('/yeu-thich', [CarController::class, 'favorites']);
$router->post('/yeu-thich/{slug}/toggle', [CarController::class, 'toggleFavorite']);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base)) ?: '/';
}
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
