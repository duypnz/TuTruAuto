<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Car;
use App\Models\CatalogOption;
use App\Models\User;
use App\Models\Voucher;
use DateTimeImmutable;
use PDOException;

final class VoucherController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->renderIndex();
    }

    public function editForm(array $params): void
    {
        $this->requireAdmin();
        $voucher = (new Voucher())->findById((int) ($params['id'] ?? 0));
        if (!$voucher) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->renderIndex($voucher);
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            $this->flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/voucher');
        }

        [$data, $errors] = $this->validate($_POST);
        if ($errors) {
            $this->renderIndex(null, $data, $errors);
            return;
        }

        try {
            (new Voucher())->create($data);
            $this->flash('Đã thêm mã giảm giá.');
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                $this->flash('Mã giảm giá này đã tồn tại.');
                $this->redirect('/admin/voucher');
            }
            throw $error;
        }
        $this->redirect('/admin/voucher');
    }

    public function update(array $params): void
    {
        $this->requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $voucher = (new Voucher())->findById($id);
        if (!$voucher) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            $this->flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/voucher/' . $id . '/sua');
        }

        [$data, $errors] = $this->validate($_POST);
        if ($errors) {
            $this->renderIndex($voucher, $data, $errors);
            return;
        }

        try {
            (new Voucher())->update($id, $data);
            $this->flash('Đã cập nhật mã giảm giá.');
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                $this->renderIndex($voucher, $data, ['code' => 'Mã giảm giá này đã tồn tại.']);
                return;
            }
            throw $error;
        }
        $this->redirect('/admin/voucher');
    }

    public function delete(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn.';
            return;
        }

        (new Voucher())->softDelete((int) ($params['id'] ?? 0));
        $this->flash('Đã xóa mã giảm giá. Lịch sử sử dụng vẫn được giữ lại.');
        $this->redirect('/admin/voucher');
    }

    private function renderIndex(?array $editing = null, array $form = [], array $errors = []): void
    {
        $filters = [
            'keyword' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];
        $vouchers = new Voucher();
        $this->render('admin/vouchers/index', [
            'title' => 'Quản lý mã giảm giá',
            'vouchers' => $vouchers->allForAdmin($filters),
            'filters' => $filters,
            'editing' => $editing,
            'form' => $form !== [] ? $form : $this->formValues($editing),
            'errors' => $errors,
            'brands' => (new CatalogOption())->values('brand'),
            'cars' => (new Car())->allForAdmin(),
            'flash' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    private function formValues(?array $voucher): array
    {
        if ($voucher === null) {
            $now = new DateTimeImmutable();
            return [
                'code' => '',
                'name' => '',
                'discount_type' => 'percent',
                'discount_value' => '5',
                'max_discount' => '',
                'minimum_order' => '0',
                'scope_type' => 'all',
                'scope_value' => '',
                'user_email' => '',
                'starts_at' => $now->format('Y-m-d\TH:i'),
                'ends_at' => $now->modify('+30 days')->format('Y-m-d\TH:i'),
                'usage_limit' => '',
                'usage_per_user' => '',
                'is_active' => true,
            ];
        }

        return [
            'code' => (string) $voucher['code'],
            'name' => (string) ($voucher['name'] ?? ''),
            'discount_type' => (string) $voucher['discount_type'],
            'discount_value' => (string) $voucher['discount_value'],
            'max_discount' => $voucher['max_discount'] !== null ? (string) $voucher['max_discount'] : '',
            'minimum_order' => (string) $voucher['minimum_order'],
            'scope_type' => (string) $voucher['scope_type'],
            'scope_value' => (string) ($voucher['scope_value'] ?? ''),
            'user_email' => (string) ($voucher['user_email'] ?? ''),
            'starts_at' => $this->inputDate((string) $voucher['starts_at']),
            'ends_at' => $this->inputDate((string) $voucher['ends_at']),
            'usage_limit' => $voucher['usage_limit'] !== null ? (string) $voucher['usage_limit'] : '',
            'usage_per_user' => $voucher['usage_per_user'] !== null ? (string) $voucher['usage_per_user'] : '',
            'is_active' => (int) $voucher['is_active'] === 1,
        ];
    }

    private function validate(array $post): array
    {
        $errors = [];
        $data = [];
        $code = strtoupper(trim((string) ($post['code'] ?? '')));
        if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) {
            $errors['code'] = 'Mã gồm 3-40 ký tự A-Z, 0-9, gạch dưới hoặc gạch ngang.';
        }
        $data['code'] = $code;

        $name = trim((string) ($post['name'] ?? ''));
        if (mb_strlen($name) > 120) {
            $errors['name'] = 'Tên hiển thị tối đa 120 ký tự.';
        }
        $data['name'] = $name;

        $type = (string) ($post['discount_type'] ?? 'percent');
        if (!in_array($type, ['percent', 'fixed'], true)) {
            $errors['discount_type'] = 'Loại giảm giá không hợp lệ.';
            $type = 'percent';
        }
        $data['discount_type'] = $type;

        $value = filter_var($post['discount_value'] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || $value <= 0 || ($type === 'percent' && $value > 100)) {
            $errors['discount_value'] = $type === 'percent'
                ? 'Giảm theo phần trăm phải từ 1 đến 100.'
                : 'Số tiền giảm phải là số nguyên dương.';
        }
        $data['discount_value'] = $value === false ? 0 : $value;

        $maxDiscount = $this->optionalInt($post['max_discount'] ?? '', $errors, 'max_discount', 'Trần giảm giá');
        $data['max_discount'] = $maxDiscount;

        $minimumOrder = filter_var($post['minimum_order'] ?? 0, FILTER_VALIDATE_INT);
        if ($minimumOrder === false || $minimumOrder < 0) {
            $errors['minimum_order'] = 'Giá trị đơn tối thiểu không hợp lệ.';
        }
        $data['minimum_order'] = $minimumOrder === false ? 0 : $minimumOrder;

        $scopeType = (string) ($post['scope_type'] ?? 'all');
        if (!in_array($scopeType, ['all', 'brand', 'car'], true)) {
            $errors['scope_type'] = 'Phạm vi áp dụng không hợp lệ.';
            $scopeType = 'all';
        }
        $data['scope_type'] = $scopeType;

        $scopeValue = trim((string) ($post['scope_value'] ?? ''));
        if ($scopeType !== 'all') {
            if ($scopeValue === '') {
                $errors['scope_value'] = 'Vui lòng nhập hãng xe hoặc chọn mẫu xe.';
            } elseif ($scopeType === 'brand' && !in_array($scopeValue, (new CatalogOption())->values('brand'), true)) {
                $errors['scope_value'] = 'Hãng xe không có trong danh mục.';
            } elseif ($scopeType === 'car' && (new Car())->findBySlug($scopeValue) === null) {
                $errors['scope_value'] = 'Mẫu xe không tồn tại.';
            }
        }
        $data['scope_value'] = $scopeType === 'all' ? null : $scopeValue;

        $userEmail = strtolower(trim((string) ($post['user_email'] ?? '')));
        $userId = null;
        if ($userEmail !== '') {
            $user = filter_var($userEmail, FILTER_VALIDATE_EMAIL) ? (new User())->findByEmail($userEmail) : null;
            if (!$user) {
                $errors['user_email'] = 'Không tìm thấy tài khoản với email này.';
            } else {
                $userId = (int) $user['id'];
            }
        }
        $data['user_id'] = $userId;
        $data['user_email'] = $userEmail;

        $startsAtInput = trim((string) ($post['starts_at'] ?? ''));
        $endsAtInput = trim((string) ($post['ends_at'] ?? ''));
        $data['starts_at_form'] = $startsAtInput;
        $data['ends_at_form'] = $endsAtInput;
        $startsAt = $this->parseDateTime($startsAtInput);
        $endsAt = $this->parseDateTime($endsAtInput);
        if ($startsAt === null || $endsAt === null) {
            $errors['period'] = 'Vui lòng nhập thời gian bắt đầu và kết thúc hợp lệ.';
        } elseif ($startsAt >= $endsAt) {
            $errors['period'] = 'Thời gian kết thúc phải sau thời gian bắt đầu.';
        }
        $data['starts_at'] = $startsAt ?? date('Y-m-d H:i:s');
        $data['ends_at'] = $endsAt ?? date('Y-m-d H:i:s', strtotime('+1 day'));

        $data['is_active'] = isset($post['is_active']);
        $data['usage_limit'] = $this->optionalInt($post['usage_limit'] ?? '', $errors, 'usage_limit', 'Tổng lượt dùng');
        $data['usage_per_user'] = $this->optionalInt($post['usage_per_user'] ?? '', $errors, 'usage_per_user', 'Lượt dùng mỗi khách');

        return [$data, $errors];
    }

    private function optionalInt(mixed $value, array &$errors, string $field, string $label): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $number = filter_var($value, FILTER_VALIDATE_INT);
        if ($number === false || $number < 0) {
            $errors[$field] = $label . ' phải là số nguyên không âm.';
            return null;
        }

        return $number;
    }

    private function parseDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (
            !$date
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d\TH:i') !== $value
        ) {
            return null;
        }

        return $date->format('Y-m-d H:i:s');
    }

    private function inputDate(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);

        return $date instanceof DateTimeImmutable ? $date->format('Y-m-d\TH:i') : '';
    }

    private function flash(string $message): void
    {
        $_SESSION['_flash'] = $message;
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_string($message) ? $message : null;
    }
}
