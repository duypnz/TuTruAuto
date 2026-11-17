<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Car;

final class CarController extends Controller
{
    public function index(): void
    {
        $cars = (new Car())->search([
            'keyword' => trim((string) ($_GET['q'] ?? '')),
            'brand' => trim((string) ($_GET['brand'] ?? '')),
            'body_type' => trim((string) ($_GET['body_type'] ?? '')),
            'fuel' => trim((string) ($_GET['fuel'] ?? '')),
            'sort' => (string) ($_GET['sort'] ?? ''),
        ]);
        $this->render('cars/index', ['cars' => $cars]);
    }

    public function show(array $params): void
    {
        $car = (new Car())->findBySlug((string) $params['slug']);
        if (!$car) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->render('cars/show', ['car' => $car]);
    }

    public function favorites(): void
    {
        if (!$this->requireCustomer()) {
            return;
        }

        $favorites = (int) ($_SESSION['favorites_user_id'] ?? 0) === (int) $_SESSION['user']['id']
            ? ($_SESSION['favorites'] ?? [])
            : [];
        if (!is_array($favorites)) {
            $favorites = [];
        }
        $cars = (new Car())->findBySlugs($favorites);
        $this->render('favorites/index', ['cars' => $cars]);
    }

    public function toggleFavorite(array $params): void
    {
        if (!$this->requireCustomer()) {
            return;
        }

        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->redirect($this->redirectTarget());
        }

        $slug = trim((string) ($params['slug'] ?? ''));
        if ($slug === '') {
            $this->redirect($this->redirectTarget());
        }

        $favorites = $_SESSION['favorites'] ?? [];
        if (!is_array($favorites)) {
            $favorites = [];
        }

        if ((int) ($_SESSION['favorites_user_id'] ?? 0) !== (int) $_SESSION['user']['id']) {
            $favorites = [];
        }

        $index = array_search($slug, $favorites, true);
        if ($index === false) {
            $favorites[] = $slug;
        } else {
            unset($favorites[$index]);
            $favorites = array_values($favorites);
        }

        $_SESSION['favorites'] = $favorites;
        $_SESSION['favorites_user_id'] = (int) $_SESSION['user']['id'];
        $this->redirect($this->redirectTarget());
    }

    private function requireCustomer(): bool
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/dang-nhap');
        }

        if (($_SESSION['user']['role'] ?? '') !== 'customer' || !isset($_SESSION['user']['id'])) {
            http_response_code(403);
            $this->render('errors/403');
            return false;
        }

        return true;
    }

    private function redirectTarget(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer === '') {
            return '/';
        }

        $parsed = parse_url($referer);
        if (!is_array($parsed) || empty($parsed['path'])) {
            return '/';
        }

        $path = $parsed['path'];
        if ($path === '' || $path === '/') {
            $path = '/';
        }

        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
        return $path . $query;
    }
}
