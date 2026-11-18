<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\CarController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\Admin\CarController as AdminCarController;
use App\Controllers\CartController;

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
$router->post('/xe/{slug}/mua', [CarController::class, 'purchaseAction']);
$router->post('/xe/{slug}/danh-gia', [CarController::class, 'submitReview']);
$router->post('/voucher/kiem-tra', [CarController::class, 'checkVoucher']);
$router->get('/gio-hang', [CartController::class, 'index']);
$router->post('/gio-hang/cap-nhat', [CartController::class, 'update']);
$router->post('/gio-hang/dat-hang', [CartController::class, 'checkout']);
$router->get('/tai-khoan/don-hang', [CartController::class, 'customerOrders']);
$router->get('/dang-nhap', [AuthController::class, 'loginForm']);
$router->post('/dang-nhap', [AuthController::class, 'login']);
$router->get('/dang-ky', [AuthController::class, 'registerForm']);
$router->post('/dang-ky', [AuthController::class, 'register']);
$router->post('/dang-xuat', [AuthController::class, 'logout']);
$router->get('/tai-khoan/ho-so', [AuthController::class, 'profile']);
$router->post('/tai-khoan/ho-so', [AuthController::class, 'updateProfile']);
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/yeu-thich', [CarController::class, 'favorites']);
$router->post('/yeu-thich/{slug}/toggle', [CarController::class, 'toggleFavorite']);
$router->get('/admin/xe', [AdminCarController::class, 'index']);
$router->get('/admin/don-hang', [CartController::class, 'adminOrders']);
$router->post('/admin/don-hang/{id}/trang-thai', [CartController::class, 'updateOrderStatus']);
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
