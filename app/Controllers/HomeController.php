<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Car;
use App\Models\CatalogOption;

final class HomeController extends Controller
{
    public function index(): void
    {
        $cars = (new Car())->featured(12);
        $bannerCars = [];
        foreach (['Lamborghini', 'Porsche', 'Mercedes-Benz', 'BMW'] as $brand) {
            foreach ($cars as $car) {
                if (strcasecmp(trim((string) ($car['brand'] ?? '')), $brand) === 0) {
                    $bannerCars[] = $car;
                    break;
                }
            }
        }
        foreach ($cars as $car) {
            if (count($bannerCars) >= 4) {
                break;
            }
            $alreadyAdded = array_filter(
                $bannerCars,
                static fn (array $bannerCar): bool => (int) $bannerCar['id'] === (int) $car['id']
            );
            if ($alreadyAdded === []) {
                $bannerCars[] = $car;
            }
        }
        $catalog = new CatalogOption();
        $success = $_SESSION['_success'] ?? null;
        unset($_SESSION['_success']);
        $error = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        $this->render('home/index', [
            'cars' => $cars,
            'bannerCars' => $bannerCars,
            'brands' => $catalog->values('brand'),
            'bodyTypes' => $catalog->values('body_type'),
            'success' => is_string($success) ? $success : null,
            'error' => is_string($error) ? $error : null,
            'csrf' => Csrf::token(),
        ]);
    }

    public function about(): void
    {
        $this->render('home/about', [
            'csrf' => Csrf::token(),
            'featuredCars' => (new Car())->featured(2),
        ]);
    }
}
