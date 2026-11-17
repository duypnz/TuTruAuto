<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, 'customer', 'active')");
        $stmt->execute(['name' => $name, 'email' => $email, 'password' => $passwordHash]);
        return (int) $this->db->lastInsertId();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function findProfile(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, phone, address FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $id, string $name, string $phone, string $address): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :id');
        $stmt->execute(['name' => $name, 'phone' => $phone !== '' ? $phone : null, 'address' => $address !== '' ? $address : null, 'id' => $id]);
    }
}
