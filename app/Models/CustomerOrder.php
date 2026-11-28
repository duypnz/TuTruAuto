<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CustomerOrder
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $order, array $items, bool $manageTransaction = true): int
    {
        if ($manageTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO customer_orders
                    (user_id, order_type, status, customer_name, phone, message,
                         voucher_code, subtotal, discount_total, total, deposit_amount, delivery_at, payment_info_json)
                 VALUES
                    (:user_id, :order_type, :status, :customer_name, :phone, :message,
                         :voucher_code, :subtotal, :discount_total, :total, :deposit_amount, :delivery_at, :payment_info_json)'
            );
            $stmt->execute([
                'user_id' => $order['user_id'] ?? null,
                'order_type' => $order['order_type'],
                'status' => $order['status'],
                'customer_name' => $order['customer_name'],
                'phone' => $order['phone'],
                'message' => $order['message'] !== '' ? $order['message'] : null,
                'voucher_code' => $order['voucher_code'] !== '' ? $order['voucher_code'] : null,
                'subtotal' => $order['subtotal'],
                'discount_total' => $order['discount_total'],
                'total' => $order['total'],
                'deposit_amount' => $order['deposit_amount'] ?? null,
                'delivery_at' => $order['delivery_at'] ?? null,
                'payment_info_json' => json_encode(
                    $order['payment_info'] ?? [],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                ),
            ]);
            $orderId = (int) $this->db->lastInsertId();
            $itemStmt = $this->db->prepare(
                'INSERT INTO customer_order_items
                    (order_id, car_id, car_name, car_image, quantity, unit_price, options_json)
                 VALUES
                    (:order_id, :car_id, :car_name, :car_image, :quantity, :unit_price, :options_json)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'car_id' => $item['car_id'],
                    'car_name' => $item['name'],
                    'car_image' => $item['image_url'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'options_json' => json_encode($item['options'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ]);
            }
            if ($manageTransaction) {
                $this->db->commit();
            }

            return $orderId;
        } catch (\Throwable $error) {
            if ($manageTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function findForCustomer(int $userId): array
    {
        return $this->findOrders(
            "WHERE o.user_id = :user_id AND o.order_type = 'deposit'",
            ['user_id' => $userId]
        );
    }

    public function findForCustomerById(int $userId, int $orderId): ?array
    {
        $orders = $this->findOrders(
            "WHERE o.user_id = :user_id AND o.order_type = 'deposit' AND o.id = :order_id",
            ['user_id' => $userId, 'order_id' => $orderId]
        );

        return $orders[0] ?? null;
    }

    public function allForAdmin(): array
    {
        return $this->searchForAdmin([]);
    }

    public function searchForAdmin(array $filters): array
    {
        $where = [];
        $params = [];
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $where[] = '(o.customer_name LIKE :name_search
                OR o.phone LIKE :phone_search
                OR u.email LIKE :email_search
                OR CAST(o.id AS CHAR) LIKE :order_search
                OR EXISTS (
                    SELECT 1 FROM customer_order_items search_item
                    WHERE search_item.order_id = o.id
                      AND search_item.car_name LIKE :car_search
                ))';
            $search = '%' . $keyword . '%';
            $params['name_search'] = $search;
            $params['phone_search'] = $search;
            $params['email_search'] = $search;
            $orderNumber = str_starts_with($keyword, '#') ? substr($keyword, 1) : $keyword;
            $params['order_search'] = '%' . ($orderNumber !== '' ? $orderNumber : $keyword) . '%';
            $params['car_search'] = $search;
        }
        $statuses = ['pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled'];
        if (in_array($filters['status'] ?? '', $statuses, true)) {
            $where[] = 'o.status = :status';
            $params['status'] = $filters['status'];
        }
        foreach (['from' => '>=', 'to' => '<'] as $dateFilter => $operator) {
            $date = (string) ($filters[$dateFilter] ?? '');
            if ($date !== '') {
                $parameter = $dateFilter . '_date';
                $where[] = $dateFilter === 'to'
                    ? 'o.created_at < DATE_ADD(:' . $parameter . ', INTERVAL 1 DAY)'
                    : 'o.created_at ' . $operator . ' :' . $parameter;
                $params[$parameter] = $date;
            }
        }
        $condition = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        return $this->findOrders($condition, $params);
    }

    public function monthlyStatistics(int $months = 12): array
    {
        $months = max(1, min($months, 24));
        $firstMonth = new \DateTimeImmutable('first day of this month');
        $startMonth = $firstMonth->modify('-' . ($months - 1) . ' months');
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    SUM(CASE WHEN status <> 'cancelled' THEN 1 ELSE 0 END) AS order_count,
                    COALESCE(SUM(CASE WHEN status = 'purchased' THEN total ELSE 0 END), 0) AS revenue
             FROM customer_orders
             WHERE created_at >= :start_at
               AND created_at < :end_at
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month_key"
        );
        $stmt->execute([
            'start_at' => $startMonth->format('Y-m-d 00:00:00'),
            'end_at' => $firstMonth->modify('+1 month')->format('Y-m-d 00:00:00'),
        ]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['month_key']] = $row;
        }

        $result = [];
        for ($offset = 0; $offset < $months; $offset++) {
            $month = $startMonth->modify('+' . $offset . ' months');
            $key = $month->format('Y-m');
            $result[] = [
                'month_key' => $key,
                'label' => $month->format('m/Y'),
                'order_count' => (int) ($rows[$key]['order_count'] ?? 0),
                'revenue' => (int) ($rows[$key]['revenue'] ?? 0),
            ];
        }

        return $result;
    }

    public function statistics(): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                (SELECT COALESCE(SUM(total), 0)
                 FROM customer_orders
                 WHERE status = :revenue_status) AS revenue,
                (SELECT COUNT(*)
                 FROM customer_orders
                 WHERE status <> :cancelled_status) AS order_count,
                (SELECT COALESCE(SUM(i.quantity), 0)
                 FROM customer_order_items i
                 INNER JOIN customer_orders o ON o.id = i.order_id
                 WHERE o.status = :sold_status) AS sold_cars'
        );
        $stmt->execute([
            'revenue_status' => 'purchased',
            'cancelled_status' => 'cancelled',
            'sold_status' => 'purchased',
        ]);
        $statistics = $stmt->fetch() ?: [];

        return [
            'revenue' => (int) ($statistics['revenue'] ?? 0),
            'order_count' => (int) ($statistics['order_count'] ?? 0),
            'sold_cars' => (int) ($statistics['sold_cars'] ?? 0),
        ];
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE customer_orders SET status = :status WHERE id = :id'
        );
        $stmt->execute(['id' => $orderId, 'status' => $status]);

        return $stmt->rowCount() > 0;
    }

    public function updateManagementDetails(
        int $orderId,
        string $status,
        ?string $deliveryAt
    ): ?array {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT o.status, o.customer_name, u.email
                 FROM customer_orders o
                 LEFT JOIN users u ON u.id = o.user_id
                 WHERE o.id = :id
                 FOR UPDATE'
            );
            $stmt->execute(['id' => $orderId]);
            $current = $stmt->fetch();
            if (!$current) {
                $this->db->commit();
                return null;
            }

            $itemStmt = $this->db->prepare(
                'SELECT car_name AS name FROM customer_order_items WHERE order_id = :order_id'
            );
            $itemStmt->execute(['order_id' => $orderId]);
            $items = $itemStmt->fetchAll();

            $updateStmt = $this->db->prepare(
                'UPDATE customer_orders
                 SET status = :status, delivery_at = :delivery_at
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'id' => $orderId,
                'status' => $status,
                'delivery_at' => $deliveryAt,
            ]);
            $this->db->commit();

            return [
                'status_changed' => $current['status'] !== $status,
                'order' => [
                    'id' => $orderId,
                    'status' => $status,
                    'customer_name' => $current['customer_name'],
                    'email' => $current['email'],
                    'items' => $items,
                ],
            ];
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    private function findOrders(string $where, array $params): array
    {
        $sql = 'SELECT o.*, u.email,
                       i.id AS item_id, i.car_id, i.car_name, i.car_image,
                       i.quantity, i.unit_price, i.options_json
                FROM customer_orders o
                LEFT JOIN users u ON u.id = o.user_id
                LEFT JOIN customer_order_items i ON i.order_id = o.id
                ' . $where . '
                ORDER BY o.created_at DESC, o.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = [];
        foreach ($stmt->fetchAll() as $row) {
            $id = (int) $row['id'];
            if (!isset($orders[$id])) {
                $orders[$id] = [
                    'id' => $id,
                    'user_id' => $row['user_id'],
                    'email' => $row['email'],
                    'order_type' => $row['order_type'],
                    'status' => $row['status'],
                    'customer_name' => $row['customer_name'],
                    'phone' => $row['phone'],
                    'message' => $row['message'],
                    'voucher_code' => $row['voucher_code'],
                    'subtotal' => $row['subtotal'],
                    'discount_total' => $row['discount_total'],
                    'total' => $row['total'],
                    'deposit_amount' => $row['deposit_amount'],
                    'delivery_at' => $row['delivery_at'],
                    'payment_info' => $this->decodePaymentInfo($row['payment_info_json'] ?? null),
                    'created_at' => $row['created_at'],
                    'items' => [],
                ];
            }
            if ($row['item_id'] !== null) {
                $options = json_decode((string) $row['options_json'], true);
                $orders[$id]['items'][] = [
                    'car_id' => (int) $row['car_id'],
                    'name' => $row['car_name'],
                    'image_url' => $row['car_image'],
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'options' => is_array($options) ? $options : [],
                ];
            }
        }

        return array_values($orders);
    }

    private function decodePaymentInfo(mixed $value): array
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
