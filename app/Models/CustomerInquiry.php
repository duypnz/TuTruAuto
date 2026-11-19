<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CustomerInquiry
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(
        ?int $carId,
        ?int $userId,
        string $type,
        string $name,
        string $phone,
        ?string $email,
        string $message
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO car_inquiries
                (car_id, user_id, inquiry_type, customer_name, phone, email, message)
             VALUES
                (:car_id, :user_id, :type, :name, :phone, :email, :message)'
        );
        $stmt->execute([
            'car_id' => $carId,
            'user_id' => $userId,
            'type' => $type,
            'name' => $name,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'message' => $message !== '' ? $message : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function searchForAdmin(string $keyword = '', string $status = ''): array
    {
        $where = [];
        $params = [];
        if ($keyword !== '') {
            $where[] = '(i.customer_name LIKE :name_search
                OR i.phone LIKE :phone_search
                OR i.email LIKE :email_search
                OR c.name LIKE :car_search)';
            $search = '%' . $keyword . '%';
            $params = [
                'name_search' => $search,
                'phone_search' => $search,
                'email_search' => $search,
                'car_search' => $search,
            ];
        }
        if (in_array($status, ['new', 'contacted', 'closed'], true)) {
            $where[] = 'i.inquiry_status = :status';
            $params['status'] = $status;
        }
        $condition = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare(
            'SELECT i.id, i.car_id, i.user_id, i.inquiry_type, i.customer_name,
                    i.phone, i.email, i.message, i.inquiry_status, i.created_at,
                    c.name AS car_name, u.email AS account_email
             FROM car_inquiries i
             LEFT JOIN cars c ON c.id = i.car_id
             LEFT JOIN users u ON u.id = i.user_id
             ' . $condition . '
             ORDER BY i.created_at DESC, i.id DESC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['new', 'contacted', 'closed'], true)) {
            throw new \InvalidArgumentException('Inquiry status is not supported.');
        }
        $stmt = $this->db->prepare(
            'UPDATE car_inquiries SET inquiry_status = :status WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);

        $exists = $this->db->prepare('SELECT 1 FROM car_inquiries WHERE id = :id LIMIT 1');
        $exists->execute(['id' => $id]);

        return (bool) $exists->fetchColumn();
    }
}
