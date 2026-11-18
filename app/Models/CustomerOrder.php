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

    public function create(array $order, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO customer_orders
                    (user_id, order_type, status, customer_name, phone, message,
                     voucher_code, subtotal, discount_total, total)
                 VALUES
                    (:user_id, :order_type, :status, :customer_name, :phone, :message,
                     :voucher_code, :subtotal, :discount_total, :total)'
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
            $this->db->commit();

            return $orderId;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function findForCustomer(int $userId): array
    {
        return $this->findOrders('WHERE o.user_id = :user_id', ['user_id' => $userId]);
    }

    public function allForAdmin(): array
    {
        return $this->findOrders('', []);
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE customer_orders SET status = :status WHERE id = :id'
        );
        $stmt->execute(['id' => $orderId, 'status' => $status]);

        return $stmt->rowCount() > 0;
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
}
