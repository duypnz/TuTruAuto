<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(mixed $value): string
{
    return number_format((int) $value, 0, ',', '.') . ' ₫';
}

function url(string $path = '/'): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($base === '' || $base === '/') ? $path : $base . $path;
}
