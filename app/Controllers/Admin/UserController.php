<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;
use PDOException;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->render('admin/users/index', [
            'title' => 'Quản lý tài khoản',
            'users' => (new User())->allForAdmin(),
            'flash' => $this->takeFlash(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'customer');
        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 120
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || mb_strlen($phone) > 30
            || ($phone !== '' && !preg_match('/^[0-9+().\s-]{7,30}$/', $phone))
            || strlen($password) < 8 || strlen($password) > 200
            || !in_array($role, ['customer', 'admin'], true)
        ) {
            $_SESSION['_flash'] = 'Kiểm tra lại họ tên, email, số điện thoại, quyền và mật khẩu (ít nhất 8 ký tự).';
            $this->redirect('/admin/tai-khoan');
        }

        if ((new User())->findByEmail($email)) {
            $_SESSION['_flash'] = 'Email này đã được sử dụng.';
            $this->redirect('/admin/tai-khoan');
        }

        try {
            (new User())->createManaged($name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                $_SESSION['_flash'] = 'Email này đã được sử dụng.';
                $this->redirect('/admin/tai-khoan');
            }
            throw $error;
        }

        $_SESSION['_flash'] = 'Đã tạo tài khoản. Hãy gửi thông tin đăng nhập ban đầu cho người dùng qua kênh an toàn.';
        $this->redirect('/admin/tai-khoan');
    }

    public function updateAccess(array $params): void
    {
        $this->requireAdmin();
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $users = new User();
        $target = $users->findAccountForAdmin($id);
        if (!$target) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $role = (string) ($_POST['role'] ?? '');
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($role, ['customer', 'admin'], true) || !in_array($status, ['active', 'inactive', 'locked'], true)) {
            $_SESSION['_flash'] = 'Quyền hoặc trạng thái tài khoản không hợp lệ.';
            $this->redirect('/admin/tai-khoan');
        }
        if (
            $id === (int) $_SESSION['user']['id']
            && ($role !== 'admin' || $status !== 'active')
        ) {
            $_SESSION['_flash'] = 'Không thể hạ quyền hoặc khóa tài khoản đang đăng nhập.';
            $this->redirect('/admin/tai-khoan');
        }
        if (
            $target['role'] === 'admin'
            && $target['status'] === 'active'
            && ($role !== 'admin' || $status !== 'active')
            && $users->activeAdminCount() <= 1
        ) {
            $_SESSION['_flash'] = 'Không thể khóa hoặc hạ quyền quản trị viên đang hoạt động cuối cùng.';
            $this->redirect('/admin/tai-khoan');
        }

        $users->updateManagedAccess($id, $role, $status);
        $_SESSION['_flash'] = 'Đã cập nhật quyền và trạng thái tài khoản.';
        $this->redirect('/admin/tai-khoan');
    }

    private function takeFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_string($message) ? $message : null;
    }
}
