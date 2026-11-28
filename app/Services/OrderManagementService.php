<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DbTransaction;
use App\Exceptions\OrderException;
use App\Models\CustomerOrder;
use App\Models\OrderStatusHistory;

final class OrderManagementService
{
    private const STATUSES = ['pending', 'deposit_pending', 'deposit_paid', 'purchased', 'cancelled', 'refunded'];
    private const TRANSITIONS = [
        'pending' => ['deposit_pending', 'deposit_paid', 'cancelled'],
        'deposit_pending' => ['deposit_paid', 'cancelled'],
        'deposit_paid' => ['deposit_pending', 'purchased', 'cancelled', 'refunded'],
        'purchased' => [],
        'cancelled' => ['refunded'],
        'refunded' => [],
    ];
    private const LABELS = [
        'pending' => 'Chờ xác nhận',
        'deposit_pending' => 'Chờ xác nhận đặt cọc',
        'deposit_paid' => 'Đã đặt cọc',
        'purchased' => 'Đã mua xe',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn cọc',
    ];

    private CustomerOrder $orders;
    private OrderStatusHistory $history;
    private DbTransaction $transaction;

    public function __construct(
        ?CustomerOrder $orders = null,
        ?OrderStatusHistory $history = null,
        ?DbTransaction $transaction = null
    ) {
        $this->orders = $orders ?? new CustomerOrder();
        $this->history = $history ?? new OrderStatusHistory();
        $this->transaction = $transaction ?? new DbTransaction();
    }

    public function updateDetails(
        int $orderId,
        string $status,
        ?string $deliveryAt,
        ?int $actorId,
        ?string $actorName
    ): void {
        $this->transaction->run(function () use ($orderId, $status, $deliveryAt, $actorId, $actorName): void {
            $order = $this->findOrder($orderId);
            $current = (string) $order['status'];
            if (in_array($current, ['cancelled', 'refunded'], true)) {
                throw new OrderException('Đơn đã hủy hoặc hoàn cọc; hãy dùng thao tác hủy/hoàn cọc.');
            }
            if (!in_array($status, self::STATUSES, true)) {
                throw new OrderException('Trạng thái đơn hàng không hợp lệ.');
            }
            if ($current !== $status) {
                $this->assertTransition($current, $status);
            }

            $this->orders->applyManagementChanges($orderId, [
                'status' => $status,
                'delivery_at' => $deliveryAt,
            ]);
            $note = $deliveryAt !== null
                ? 'Cập nhật lịch nhận xe: ' . $deliveryAt
                : 'Cập nhật thông tin quản lý đơn hàng.';
            $this->history->record(
                $orderId,
                $current,
                $status,
                'status_update',
                $note,
                $actorId,
                $actorName,
                ['delivery_at' => $deliveryAt]
            );
        });
    }

    public function verifyPayment(
        int $orderId,
        string $reference,
        string $note,
        bool $verified,
        ?int $actorId,
        ?string $actorName
    ): void {
        $this->transaction->run(function () use ($orderId, $reference, $note, $verified, $actorId, $actorName): void {
            $order = $this->findOrder($orderId);
            $current = (string) $order['status'];
            if (in_array($current, ['purchased', 'refunded'], true)) {
                throw new OrderException('Không thể đổi đối soát thanh toán khi đơn đã hoàn tất hoặc đã hoàn cọc.');
            }
            $target = $verified ? 'deposit_paid' : 'deposit_pending';
            if ($current !== $target) {
                $this->assertTransition($current, $target);
            }

            $reference = trim($reference);
            $note = trim($note);
            $now = date('Y-m-d H:i:s');
            $this->orders->applyManagementChanges($orderId, [
                'status' => $target,
                'payment_reference' => $reference !== '' ? mb_substr($reference, 0, 120) : null,
                'payment_note' => $note !== '' ? mb_substr($note, 0, 500) : null,
                'payment_verified_at' => $verified ? $now : null,
                'payment_verified_by' => $verified ? $actorId : null,
            ]);
            $historyNote = $verified
                ? 'Xác nhận đã nhận tiền cọc' . ($reference !== '' ? ' · tham chiếu ' . $reference : '') . '.'
                : 'Hủy xác nhận tiền cọc, chuyển đơn về trạng thái chờ cọc.';
            if ($note !== '') {
                $historyNote .= ' Ghi chú: ' . $note;
            }
            $this->history->record(
                $orderId,
                $current,
                $target,
                'payment_verify',
                $historyNote,
                $actorId,
                $actorName,
                ['reference' => $reference !== '' ? $reference : null, 'verified' => $verified]
            );
        });
    }

    public function cancel(
        int $orderId,
        string $reason,
        ?int $actorId,
        ?string $actorName
    ): void {
        $reason = trim($reason);
        if ($reason === '') {
            throw new OrderException('Vui lòng nhập lý do hủy đơn.');
        }
        $this->transaction->run(function () use ($orderId, $reason, $actorId, $actorName): void {
            $order = $this->findOrder($orderId);
            $current = (string) $order['status'];
            $this->assertTransition($current, 'cancelled');

            $this->orders->applyManagementChanges($orderId, [
                'status' => 'cancelled',
                'cancelled_at' => $order['cancelled_at'] ?? date('Y-m-d H:i:s'),
                'cancel_reason' => mb_substr($reason, 0, 500),
            ]);
            $historyNote = 'Hủy đơn: ' . $reason;
            if ($current === 'deposit_paid') {
                $historyNote .= '. Cần hoàn tiền cọc cho khách hàng.';
            }
            $this->history->record(
                $orderId,
                $current,
                'cancelled',
                'cancel',
                $historyNote,
                $actorId,
                $actorName,
                ['refund_required' => $current === 'deposit_paid']
            );
        });
    }

    public function refund(
        int $orderId,
        int $amount,
        string $method,
        string $note,
        ?int $actorId,
        ?string $actorName
    ): void {
        if ($amount <= 0) {
            throw new OrderException('Số tiền hoàn cọc phải lớn hơn 0.');
        }
        if (!in_array($method, ['bank', 'cash'], true)) {
            throw new OrderException('Phương thức hoàn tiền không hợp lệ.');
        }
        $this->transaction->run(function () use ($orderId, $amount, $method, $note, $actorId, $actorName): void {
            $order = $this->findOrder($orderId);
            $current = (string) $order['status'];
            $this->assertTransition($current, 'refunded');
            $depositAmount = (int) ($order['deposit_amount'] ?? 0);
            $alreadyRefunded = (int) ($order['refund_amount'] ?? 0);
            if ($depositAmount > 0 && $amount > $depositAmount - $alreadyRefunded) {
                throw new OrderException('Số tiền hoàn không được vượt quá tiền cọc chưa hoàn.');
            }

            $note = trim($note);
            $now = date('Y-m-d H:i:s');
            $this->orders->applyManagementChanges($orderId, [
                'status' => 'refunded',
                'cancelled_at' => $order['cancelled_at'] ?? $now,
                'refund_amount' => $alreadyRefunded + $amount,
                'refund_method' => $method,
                'refund_note' => $note !== '' ? mb_substr($note, 0, 500) : null,
                'refunded_at' => $now,
                'refunded_by' => $actorId,
            ]);
            $historyNote = 'Hoàn cọc '
                . number_format($amount, 0, ',', '.') . ' ₫ bằng '
                . ($method === 'bank' ? 'chuyển khoản' : 'tiền mặt') . '.';
            if ($note !== '') {
                $historyNote .= ' Ghi chú: ' . $note;
            }
            $this->history->record(
                $orderId,
                $current,
                'refunded',
                'refund',
                $historyNote,
                $actorId,
                $actorName,
                ['amount' => $amount, 'total_refunded' => $alreadyRefunded + $amount, 'method' => $method]
            );
        });
    }

    private function findOrder(int $orderId): array
    {
        $order = $this->orders->findRawById($orderId);
        if (!$order) {
            throw new OrderException('Không tìm thấy đơn hàng.');
        }

        return $order;
    }

    private function assertTransition(string $from, string $to): void
    {
        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new OrderException(
                'Không thể chuyển từ "' . self::LABELS[$from] . '" sang "' . self::LABELS[$to] . '".'
            );
        }
    }
}
