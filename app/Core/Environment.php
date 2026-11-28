<?php
declare(strict_types=1);

namespace App\Core;

final class Environment
{
    private static ?array $values = null;

    public static function get(string $key, string $default = ''): string
    {
        $environmentValue = getenv($key);
        if ($environmentValue !== false) {
            return $environmentValue;
        }

        return self::values()[$key] ?? $default;
    }

    private static function values(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $values = [];
        $file = dirname(__DIR__, 2) . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $values[trim($key)] = trim(trim($value), "\"'");
            }
        }

        self::$values = $values;
        return self::$values;
    }
}
