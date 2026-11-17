<?php declare(strict_types=1);

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
    $base = rtrim(
        str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')),
        '/'
    );

    return ($base === '' || $base === '/') ? $path : $base . $path;
}

function image_src(mixed $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    return url('/' . ltrim($value, '/'));
}

function isCustomerLoggedIn(): bool
{
    return isset($_SESSION['user'])
        && ($_SESSION['user']['role'] ?? '') === 'customer'
        && isset($_SESSION['user']['id']);
}

function isFavorite(string $slug): bool
{
    if (
        !isCustomerLoggedIn()
        || (int) ($_SESSION['favorites_user_id'] ?? 0)
        !== (int) $_SESSION['user']['id']
        || !isset($_SESSION['favorites'])
        || !is_array($_SESSION['favorites'])
    ) {
        return false;
    }

    return in_array($slug, $_SESSION['favorites'], true);
}

function favoriteCount(): int
{
    if (
        !isCustomerLoggedIn()
        || (int) ($_SESSION['favorites_user_id'] ?? 0)
        !== (int) $_SESSION['user']['id']
        || !isset($_SESSION['favorites'])
        || !is_array($_SESSION['favorites'])
    ) {
        return 0;
    }

    return count($_SESSION['favorites']);
}
