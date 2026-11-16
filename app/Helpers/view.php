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

function isFavorite(string $slug): bool
{
    if (!isCustomerLoggedIn()
        || (int) ($_SESSION['favorites_user_id'] ?? 0) !== (int) $_SESSION['user']['id']
        || !isset($_SESSION['favorites'])
        || !is_array($_SESSION['favorites'])) {
        return false;
    }

    return in_array($slug, $_SESSION['favorites'], true);
}

function isCustomerLoggedIn(): bool
{
    return isset($_SESSION['user'])
        && ($_SESSION['user']['role'] ?? '') === 'customer'
        && isset($_SESSION['user']['id']);
}

function favoriteCount(): int
{
    if (!isCustomerLoggedIn()
        || (int) ($_SESSION['favorites_user_id'] ?? 0) !== (int) $_SESSION['user']['id']
        || !isset($_SESSION['favorites'])
        || !is_array($_SESSION['favorites'])) {
        return 0;
    }

    return count($_SESSION['favorites']);
}
