<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CatalogOption
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id, option_type, name, is_active, created_at
             FROM car_catalog_options
             ORDER BY option_type, name'
        )->fetchAll();
    }

    public function values(string $type): array
    {
        if (!in_array($type, ['brand', 'body_type'], true)) {
            throw new \InvalidArgumentException('Catalog option type is not supported.');
        }
        $column = $type === 'brand' ? 'brand' : 'body_type';
        $stmt = $this->db->prepare(
            'SELECT name FROM car_catalog_options
             WHERE option_type = :type AND is_active = 1
             UNION
             SELECT ' . $column . ' AS name FROM cars
             ORDER BY name'
        );
        $stmt->execute(['type' => $type]);

        return array_map(static fn (array $row): string => (string) $row['name'], $stmt->fetchAll());
    }

    public function add(string $type, string $name): bool
    {
        if (!in_array($type, ['brand', 'body_type'], true)) {
            throw new \InvalidArgumentException('Catalog option type is not supported.');
        }
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO car_catalog_options (option_type, name)
             VALUES (:type, :name)'
        );
        $stmt->execute(['type' => $type, 'name' => $name]);

        return $stmt->rowCount() > 0;
    }
}
