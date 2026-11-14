<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Car
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function featured(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT * FROM cars WHERE status = :status ORDER BY featured DESC, created_at DESC LIMIT :limit');
        $stmt->bindValue(':status', 'available');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM cars')->fetchColumn();
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT * FROM cars WHERE status = :status';
        $params = ['status' => 'available'];
        foreach (['brand' => 'brand', 'body_type' => 'body_type', 'fuel' => 'fuel'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $sql .= " AND {$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }
        if (($filters['keyword'] ?? '') !== '') {
            $sql .= ' AND (name LIKE :keyword_name OR brand LIKE :keyword_brand)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
            $params['keyword_name'] = $params['keyword'];
            $params['keyword_brand'] = $params['keyword'];
            unset($params['keyword']);
        }
        $sql .= match ($filters['sort'] ?? '') {
            'price_asc' => ' ORDER BY price ASC',
            'price_desc' => ' ORDER BY price DESC',
            default => ' ORDER BY featured DESC, created_at DESC',
        };
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cars WHERE slug = :slug AND status = :status LIMIT 1');
        $stmt->execute(['slug' => $slug, 'status' => 'available']);
        return $stmt->fetch() ?: null;
    }
}
