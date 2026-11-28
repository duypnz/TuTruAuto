<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\VoucherException;
use App\Models\Voucher;

final class VoucherService
{
    private Voucher $vouchers;

    public function __construct(?Voucher $vouchers = null)
    {
        $this->vouchers = $vouchers ?? new Voucher();
    }

    public function quote(
        string $code,
        int $subtotal,
        ?int $userId,
        ?string $brand,
        ?string $carSlug
    ): array {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return $this->invalid('Vui lòng nhập mã giảm giá.');
        }

        $voucher = $this->vouchers->findActiveByCode($code);
        if (!$voucher) {
            return $this->invalid('Mã giảm giá không tồn tại, đã hết hạn hoặc đang tạm ngưng.');
        }

        try {
            $this->assertEligible($voucher, $subtotal, $userId, $brand, $carSlug);
        } catch (VoucherException $error) {
            return $this->invalid($error->getMessage());
        }

        $discount = $this->calculateDiscount($voucher, $subtotal);

        return [
            'valid' => true,
            'code' => (string) $voucher['code'],
            'discount' => $discount,
            'message' => 'Áp dụng thành công: giảm ' . number_format($discount, 0, ',', '.') . ' ₫.',
            'voucher' => $voucher,
        ];
    }

    public function apply(
        string $code,
        int $subtotal,
        ?int $userId,
        ?string $brand,
        ?string $carSlug
    ): array {
        $code = strtoupper(trim($code));
        if ($code === '') {
            throw new VoucherException('Vui lòng nhập mã giảm giá.');
        }

        $voucher = $this->vouchers->lockByCode($code);
        if (!$voucher) {
            throw new VoucherException('Mã giảm giá không tồn tại, đã hết hạn hoặc đang tạm ngưng.');
        }

        $this->assertEligible($voucher, $subtotal, $userId, $brand, $carSlug);

        return [
            'voucher' => $voucher,
            'discount' => $this->calculateDiscount($voucher, $subtotal),
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
        $this->vouchers->recordUsage($voucherId, $code, $userId, $orderId, $subtotal, $discount);
    }

    private function assertEligible(
        array $voucher,
        int $subtotal,
        ?int $userId,
        ?string $brand,
        ?string $carSlug
    ): void {
        $minimumOrder = (int) $voucher['minimum_order'];
        if ($subtotal < $minimumOrder) {
            throw new VoucherException(
                'Đơn hàng chưa đạt giá trị tối thiểu '
                . number_format($minimumOrder, 0, ',', '.') . ' ₫.'
            );
        }

        $voucherUserId = isset($voucher['user_id']) ? (int) $voucher['user_id'] : 0;
        if ($voucherUserId > 0 && $voucherUserId !== $userId) {
            throw new VoucherException('Mã giảm giá này chỉ dùng được cho tài khoản khách hàng được cấp.');
        }

        $scopeType = (string) $voucher['scope_type'];
        $scopeValue = trim((string) ($voucher['scope_value'] ?? ''));
        if ($scopeType === 'brand' && mb_strtolower($scopeValue) !== mb_strtolower(trim((string) $brand))) {
            throw new VoucherException('Mã giảm giá chỉ áp dụng cho xe hãng ' . $scopeValue . '.');
        }
        if ($scopeType === 'car' && $scopeValue !== trim((string) $carSlug)) {
            throw new VoucherException('Mã giảm giá chỉ áp dụng cho một mẫu xe cụ thể.');
        }

        $counts = $this->vouchers->usageCounts((int) $voucher['id'], $userId);
        $usageLimit = isset($voucher['usage_limit']) && $voucher['usage_limit'] !== null
            ? (int) $voucher['usage_limit']
            : null;
        $usagePerUser = isset($voucher['usage_per_user']) && $voucher['usage_per_user'] !== null
            ? (int) $voucher['usage_per_user']
            : null;

        if ($usageLimit !== null && $counts['total'] >= $usageLimit) {
            throw new VoucherException('Mã giảm giá đã hết lượt sử dụng.');
        }
        if ($usagePerUser !== null && $counts['user'] >= $usagePerUser) {
            throw new VoucherException('Bạn đã dùng mã giảm giá này đủ số lượt cho phép.');
        }
    }

    private function calculateDiscount(array $voucher, int $subtotal): int
    {
        $discount = $voucher['discount_type'] === 'percent'
            ? (int) round($subtotal * (int) $voucher['discount_value'] / 100)
            : (int) $voucher['discount_value'];
        $discount = min($subtotal, max(0, $discount));

        if (!empty($voucher['max_discount'])) {
            $discount = min($discount, (int) $voucher['max_discount']);
        }

        return max(0, $discount);
    }

    private function invalid(string $message): array
    {
        return [
            'valid' => false,
            'code' => '',
            'discount' => 0,
            'message' => $message,
            'voucher' => null,
        ];
    }
}
