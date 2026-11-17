<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Car;

final class HomeController extends Controller
{
    public function index(): void
    {
        $cars = (new Car())->featured();
        $success = $_SESSION['_success'] ?? null;
        unset($_SESSION['_success']);
        $this->render('home/index', ['cars' => $cars, 'success' => is_string($success) ? $success : null]);
    }
}
