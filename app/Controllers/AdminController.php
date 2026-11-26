<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Car;
use App\Models\CustomerOrder;
use App\Models\User;

final class AdminController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/dang-nhap');
        }
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            $this->render('errors/403');
            return;
        }

        $orders = new CustomerOrder();
        $this->render('admin/dashboard', [
            'title' => 'Quản trị',
            'userCount' => (new User())->countAll(),
            'carCount' => (new Car())->countAll(),
            'salesStats' => $orders->statistics(),
            'monthlyStats' => $orders->monthlyStatistics(),
            'admin' => $_SESSION['user'],
        ]);
    }
}
