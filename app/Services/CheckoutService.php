<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DbTransaction;
use App\Models\CustomerOrder;

final class CheckoutService
{
    private VoucherService $vouchers;
    private CustomerOrder $orders;
    private DbTransaction $transaction;

    public function __construct(
        ?VoucherService $vouchers = null,
        ?CustomerOrder $orders = null,
        ?DbTransaction $transaction = null
    ) {
        $this->vouchers = $vouchers ?? new VoucherService();
        $this->orders = $orders ?? new CustomerOrder();
        $this->transaction = $transaction ?? new DbTransaction();
    }

    public function createDeposit(
        array $order,
        array $items,
        string $voucherCode,
        ?int $userId,
        ?string $brand,
        ?string $carSlug
    ): int {
        return $this->transaction->run(function () use ($order, $items, $voucherCode, $userId, $brand, $carSlug): int {
            $discount = 0;
            $voucher = null;

            if (trim($voucherCode) !== '') {
                $applied = $this->vouchers->apply($voucherCode, (int) $order['subtotal'], $userId, $brand, $carSlug);
                $voucher = $applied['voucher'];
                $discount = $applied['discount'];
                $order['voucher_code'] = (string) $voucher['code'];
            }

            $order['discount_total'] = $discount;
            $order['total'] = (int) $order['subtotal'] - $discount;
            $order['deposit_amount'] = max(1, (int) round($order['total'] * 0.1));

            $orderId = $this->orders->create($order, $items, false);

            if ($voucher !== null) {
                $this->vouchers->recordUsage(
                    (int) $voucher['id'],
                    (string) $voucher['code'],
                    $userId,
                    $orderId,
                    (int) $order['subtotal'],
                    $discount
                );
            }

            return $orderId;
        });
    }
}
