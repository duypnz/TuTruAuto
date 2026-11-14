<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function redirect(string $path): never
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $location = ($base === '' || $base === '/') ? $path : $base . $path;
        header('Location: ' . $location);
        exit;
    }

    protected function render(string $view, array $data = []): void
    {
        require_once dirname(__DIR__) . '/Helpers/view.php';
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }
}
