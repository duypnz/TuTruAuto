<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\CarController;

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
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base)) ?: '/';
}
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
