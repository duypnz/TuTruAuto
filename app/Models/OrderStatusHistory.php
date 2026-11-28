<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class OrderStatusHistory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function forOrder(int $orderId): array
    {
        return $this->forOrders([$orderId])[$orderId] ?? [];
    }

    public function forOrders(array $orderIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): int => is_scalar($id) ? (int) $id : 0, $orderIds),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_map(static fn (int $index): string => ':id_' . $index, array_keys($ids)));
        $params = [];
        foreach ($ids as $index => $id) {
            $params['id_' . $index] = $id;
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM customer_order_status_history
             WHERE order_id IN (' . $placeholders . ')
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute($params);

        $histories = [];
        foreach ($stmt->fetchAll() as $row) {
            $orderId = (int) $row['order_id'];
            $histories[$orderId][] = [
                'id' => (int) $row['id'],
                'from_status' => $row['from_status'],
                'to_status' => (string) $row['to_status'],
                'action' => (string) $row['action'],
                'note' => $row['note'],
                'details' => $this->decodeDetails($row['details_json'] ?? null),
                'actor_name' => $row['actor_name'],
                'created_at' => (string) $row['created_at'],
            ];
        }

        return $histories;
    }

    public function record(
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        string $action,
        ?string $note,
        ?int $actorId,
        ?string $actorName,
        array $details = []
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO customer_order_status_history
                (order_id, from_status, to_status, action, note, details_json, actor_id, actor_name)
             VALUES
                (:order_id, :from_status, :to_status, :action, :note, :details_json, :actor_id, :actor_name)'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action' => $action,
            'note' => $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 500) : null,
            'details_json' => $details === [] ? null : json_encode(
                $details,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ),
            'actor_id' => $actorId,
            'actor_name' => $actorName,
        ]);
    }

    private function decodeDetails(mixed $value): array
    {
        if (!is_string($value) || $value === '') {
            return [];
        }
        try {
            $decoded = json_decode($value, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
