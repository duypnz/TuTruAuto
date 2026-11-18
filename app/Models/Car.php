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
        $stmt = $this->db->prepare(
            'SELECT * FROM cars
             WHERE status = :status
             ORDER BY featured DESC, created_at DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':status', 'available');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db
            ->query('SELECT COUNT(*) FROM cars')
            ->fetchColumn();
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT * FROM cars WHERE status = :status';
        $params = ['status' => 'available'];

        foreach (
            [
                'brand' => 'brand',
                'body_type' => 'body_type',
                'fuel' => 'fuel'
            ] as $key => $column
        ) {
            if (($filters[$key] ?? '') !== '') {
                $sql .= " AND {$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        if (($filters['keyword'] ?? '') !== '') {
            $sql .= ' AND (name LIKE :keyword_name OR brand LIKE :keyword_brand)';
            $params['keyword_name'] = '%' . $filters['keyword'] . '%';
            $params['keyword_brand'] = '%' . $filters['keyword'] . '%';
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
        $stmt = $this->db->prepare(
            'SELECT * FROM cars
             WHERE slug = :slug AND status <> :status
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
            'status' => 'hidden'
        ]);

        return $stmt->fetch() ?: null;
    }

    public function related(array $car, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cars
             WHERE status = :status AND id <> :id
               AND (brand = :brand OR body_type = :body_type)
             ORDER BY (brand = :brand_order) DESC, featured DESC, created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':status', 'available');
        $stmt->bindValue(':id', (int) $car['id'], PDO::PARAM_INT);
        $stmt->bindValue(':brand', (string) $car['brand']);
        $stmt->bindValue(':body_type', (string) $car['body_type']);
        $stmt->bindValue(':brand_order', (string) $car['brand']);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function reviews(int $carId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.rating, r.content, r.created_at, u.name
             FROM car_reviews r
             INNER JOIN users u ON u.id = r.user_id
             WHERE r.car_id = :car_id
             ORDER BY r.created_at DESC
             LIMIT 30'
        );
        $stmt->execute(['car_id' => $carId]);

        return $stmt->fetchAll();
    }

    public function reviewSummary(int $carId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average
             FROM car_reviews WHERE car_id = :car_id'
        );
        $stmt->execute(['car_id' => $carId]);

        return $stmt->fetch() ?: ['total' => 0, 'average' => 0];
    }

    public function addReview(int $carId, int $userId, int $rating, string $content): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO car_reviews (car_id, user_id, rating, content)
             VALUES (:car_id, :user_id, :rating, :content)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), content = VALUES(content), created_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'car_id' => $carId,
            'user_id' => $userId,
            'rating' => $rating,
            'content' => $content,
        ]);
    }

    public function voucher(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT code, discount_type, discount_value, minimum_order
             FROM car_vouchers
             WHERE code = :code AND is_active = 1
               AND starts_at <= CURRENT_TIMESTAMP AND ends_at >= CURRENT_TIMESTAMP
             LIMIT 1'
        );
        $stmt->execute(['code' => strtoupper(trim($code))]);

        return $stmt->fetch() ?: null;
    }

    public function addInquiry(int $carId, ?int $userId, string $type, string $name, string $phone, string $message): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO car_inquiries (car_id, user_id, inquiry_type, customer_name, phone, message)
             VALUES (:car_id, :user_id, :type, :name, :phone, :message)'
        );
        $stmt->execute([
            'car_id' => $carId,
            'user_id' => $userId,
            'type' => $type,
            'name' => $name,
            'phone' => $phone,
            'message' => $message !== '' ? $message : null,
        ]);
    }

    /**
     * Lấy danh sách xe theo nhiều slug.
     * Dùng cho chức năng xe yêu thích.
     */
    public function findBySlugs(array $slugs): array
    {
        // Chỉ nhận slug dạng chuỗi, loại bỏ giá trị rỗng và trùng lặp.
        $slugs = array_values(array_unique(
            array_filter($slugs, static function ($slug): bool {
                return is_string($slug) && trim($slug) !== '';
            })
        ));

        if ($slugs === []) {
            return [];
        }

        $placeholders = [];

        foreach ($slugs as $index => $slug) {
            $placeholders[] = ':slug' . $index;
        }

        $sql = 'SELECT * FROM cars
                WHERE status = :status
                AND slug IN (' . implode(', ', $placeholders) . ')
                ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);

        $params = [
            'status' => 'available'
        ];

        foreach ($slugs as $index => $slug) {
            $params['slug' . $index] = $slug;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cars WHERE id = :id LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function allForAdmin(): array
    {
        return $this->db
            ->query('SELECT * FROM cars ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cars
            (name, slug, brand, year, body_type, fuel, transmission,
             price, image_url, badge, description, featured, status,
             condition_type, discount_price, car_code, gallery_json, video_url, details_json)
            VALUES
            (:name, :slug, :brand, :year, :body_type, :fuel, :transmission,
             :price, :image_url, :badge, :description, :featured, :status,
             :condition_type, :discount_price, :car_code, :gallery_json, :video_url, :details_json)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'brand' => $data['brand'],
            'year' => $data['year'],
            'body_type' => $data['body_type'],
            'fuel' => $data['fuel'],
            'transmission' => $data['transmission'],
            'price' => $data['price'],
            'image_url' => $data['image_url'],
            'badge' => $data['badge'] !== '' ? $data['badge'] : null,
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'featured' => $data['featured'],
            'status' => $data['status'],
            'condition_type' => $data['condition_type'],
            'discount_price' => $data['discount_price'],
            'car_code' => $data['car_code'] !== '' ? $data['car_code'] : null,
            'gallery_json' => $data['gallery_json'],
            'video_url' => $data['video_url'],
            'details_json' => $data['details_json'],
        ]);

        $id = (int) $this->db->lastInsertId();
        $code = $data['car_code'] !== '' ? $data['car_code'] : 'TTA-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
        $updateCode = $this->db->prepare('UPDATE cars SET car_code = :code WHERE id = :id');
        $updateCode->execute(['code' => $code, 'id' => $id]);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE cars SET
                name = :name,
                slug = :slug,
                brand = :brand,
                year = :year,
                body_type = :body_type,
                fuel = :fuel,
                transmission = :transmission,
                price = :price,
                image_url = :image_url,
                badge = :badge,
                description = :description,
                featured = :featured,
                status = :status,
                condition_type = :condition_type,
                discount_price = :discount_price,
                car_code = :car_code,
                gallery_json = :gallery_json,
                video_url = :video_url,
                details_json = :details_json
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'brand' => $data['brand'],
            'year' => $data['year'],
            'body_type' => $data['body_type'],
            'fuel' => $data['fuel'],
            'transmission' => $data['transmission'],
            'price' => $data['price'],
            'image_url' => $data['image_url'],
            'badge' => $data['badge'] !== '' ? $data['badge'] : null,
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'featured' => $data['featured'],
            'status' => $data['status'],
            'condition_type' => $data['condition_type'],
            'discount_price' => $data['discount_price'],
            'car_code' => $data['car_code'],
            'gallery_json' => $data['gallery_json'],
            'video_url' => $data['video_url'],
            'details_json' => $data['details_json'],
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM cars WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM cars
                 WHERE slug = :slug AND id <> :id'
            );

            $stmt->execute([
                'slug' => $slug,
                'id' => $excludeId
            ]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM cars WHERE slug = :slug'
            );

            $stmt->execute(['slug' => $slug]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public function carCodeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM cars WHERE car_code = :code';
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $base = trim($base, '-') ?: 'xe';
        $slug = $base;
        $i = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public static function slugify(string $name): string
    {
        $slug = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

        if ($slug === false) {
            $slug = $name;
        }

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug === '' ? 'xe' : $slug;
    }
}
