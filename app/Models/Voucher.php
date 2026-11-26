<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Voucher
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function allForAdmin(array $filters = []): array
    {
        $where = ['v.deleted_at IS NULL'];
        $params = [];
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $where[] = '(v.code LIKE :code_search OR v.name LIKE :name_search OR u.email LIKE :email_search)';
            $search = '%' . $keyword . '%';
            $params['code_search'] = $search;
            $params['name_search'] = $search;
            $params['email_search'] = $search;
        }

        $status = (string) ($filters['status'] ?? '');
        $now = date('Y-m-d H:i:s');
        if ($status === 'active') {
            $where[] = 'v.is_active = 1 AND v.ends_at >= :active_now';
            $params['active_now'] = $now;
        } elseif ($status === 'inactive') {
            $where[] = 'v.is_active = 0';
        } elseif ($status === 'expired') {
            $where[] = 'v.ends_at < :expired_now';
            $params['expired_now'] = $now;
        }

        $stmt = $this->db->prepare(
            'SELECT v.*, u.email AS user_email
             FROM car_vouchers v
             LEFT JOIN users u ON u.id = v.user_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY v.created_at DESC, v.id DESC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, u.email AS user_email
             FROM car_vouchers v
             LEFT JOIN users u ON u.id = v.user_id
             WHERE v.id = :id AND v.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findActiveByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM car_vouchers
             WHERE code = :code
               AND is_active = 1
               AND deleted_at IS NULL
               AND starts_at <= :starts_now
               AND ends_at >= :ends_now
             LIMIT 1'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'code' => strtoupper(trim($code)),
            'starts_now' => $now,
            'ends_now' => $now,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function lockByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM car_vouchers
             WHERE code = :code
               AND is_active = 1
               AND deleted_at IS NULL
               AND starts_at <= :starts_now
               AND ends_at >= :ends_now
             LIMIT 1
             FOR UPDATE'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'code' => strtoupper(trim($code)),
            'starts_now' => $now,
            'ends_now' => $now,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function usageCounts(int $voucherId, ?int $userId): array
    {
        $totalStmt = $this->db->prepare(
            'SELECT COUNT(*) FROM voucher_usages WHERE voucher_id = :voucher_id'
        );
        $totalStmt->execute(['voucher_id' => $voucherId]);

        $userCount = 0;
        if ($userId !== null) {
            $userStmt = $this->db->prepare(
                'SELECT COUNT(*) FROM voucher_usages
                 WHERE voucher_id = :voucher_id AND user_id = :user_id'
            );
            $userStmt->execute([
                'voucher_id' => $voucherId,
                'user_id' => $userId,
            ]);
            $userCount = (int) $userStmt->fetchColumn();
        }

        return [
            'total' => (int) $totalStmt->fetchColumn(),
            'user' => $userCount,
        ];
    }

    public function recordUsage(
        int $voucherId,
        string $code,
        ?int $userId,
        int $orderId,
        int $subtotal,
        int $discount
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO voucher_usages
                (voucher_id, code, user_id, order_id, subtotal, discount_amount)
             VALUES
                (:voucher_id, :code, :user_id, :order_id, :subtotal, :discount_amount)'
        );
        $stmt->execute([
            'voucher_id' => $voucherId,
            'code' => strtoupper(trim($code)),
            'user_id' => $userId,
            'order_id' => $orderId,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
        ]);

        $update = $this->db->prepare(
            'UPDATE car_vouchers
             SET used_count = (SELECT COUNT(*) FROM voucher_usages WHERE voucher_id = :count_id)
             WHERE id = :voucher_id'
        );
        $update->execute([
            'count_id' => $voucherId,
            'voucher_id' => $voucherId,
        ]);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO car_vouchers
                (code, name, discount_type, discount_value, max_discount, minimum_order,
                 scope_type, scope_value, user_id, starts_at, ends_at, is_active,
                 usage_limit, usage_per_user)
             VALUES
                (:code, :name, :discount_type, :discount_value, :max_discount, :minimum_order,
                 :scope_type, :scope_value, :user_id, :starts_at, :ends_at, :is_active,
                 :usage_limit, :usage_per_user)'
        );
        $stmt->execute($this->parameters($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->parameters($data);
        $params['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE car_vouchers SET
                code = :code,
                name = :name,
                discount_type = :discount_type,
                discount_value = :discount_value,
                max_discount = :max_discount,
                minimum_order = :minimum_order,
                scope_type = :scope_type,
                scope_value = :scope_value,
                user_id = :user_id,
                starts_at = :starts_at,
                ends_at = :ends_at,
                is_active = :is_active,
                usage_limit = :usage_limit,
                usage_per_user = :usage_per_user
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE car_vouchers
             SET deleted_at = :deleted_at, is_active = 0
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    private function parameters(array $data): array
    {
        return [
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => $this->nullableString($data['name'] ?? null),
            'discount_type' => (string) $data['discount_type'],
            'discount_value' => (int) $data['discount_value'],
            'max_discount' => isset($data['max_discount']) && $data['max_discount'] !== ''
                ? (int) $data['max_discount']
                : null,
            'minimum_order' => (int) $data['minimum_order'],
            'scope_type' => (string) $data['scope_type'],
            'scope_value' => $this->nullableString($data['scope_value'] ?? null),
            'user_id' => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            'starts_at' => (string) $data['starts_at'],
            'ends_at' => (string) $data['ends_at'],
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'usage_limit' => isset($data['usage_limit']) && $data['usage_limit'] !== ''
                ? (int) $data['usage_limit']
                : null,
            'usage_per_user' => isset($data['usage_per_user']) && $data['usage_per_user'] !== ''
                ? (int) $data['usage_per_user']
                : null,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
