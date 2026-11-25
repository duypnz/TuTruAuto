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

    public function allForAdmin(): array
    {
        return $this->db->query(
            'SELECT id, name, email, phone, role, status, created_at
             FROM users
             ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public function findAccountForAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, status FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function activeAdminCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'"
        )->fetchColumn();
    }

    public function createManaged(
        string $name,
        string $email,
        string $phone,
        string $passwordHash,
        string $role
    ): int {
        if (!in_array($role, ['customer', 'admin'], true)) {
            throw new \InvalidArgumentException('User role is not supported.');
        }
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, phone, password, role, status)
             VALUES (:name, :email, :phone, :password, :role, :status)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password' => $passwordHash,
            'role' => $role,
            'status' => 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateManagedAccess(int $id, string $role, string $status): bool
    {
        if (!in_array($role, ['customer', 'admin'], true) || !in_array($status, ['active', 'inactive', 'locked'], true)) {
            throw new \InvalidArgumentException('User access settings are not supported.');
        }
        $stmt = $this->db->prepare(
            'UPDATE users SET role = :role, status = :status WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'role' => $role, 'status' => $status]);

        return $stmt->rowCount() > 0;
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
