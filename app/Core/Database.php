<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?self $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $config = [];
        $file = dirname(__DIR__, 2) . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$key, $value] = explode('=', $line, 2);
                $config[trim($key)] = trim(trim($value), "\"'");
            }
        }
        $host = $config['DB_HOST'] ?? '127.0.0.1';
        $name = $config['DB_NAME'] ?? 'tu_tru_auto';
        $port = $config['DB_PORT'] ?? '3306';
        $this->connection = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $config['DB_USER'] ?? 'root', $config['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
