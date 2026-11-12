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
        $this->render('home/index', ['cars' => $cars]);
    }
}
