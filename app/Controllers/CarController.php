<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
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
}
