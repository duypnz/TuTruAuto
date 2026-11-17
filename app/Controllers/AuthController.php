<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (isset($_SESSION['user'])) $this->redirect(($_SESSION['user']['role'] ?? '') === 'admin' ? '/admin' : '/tai-khoan/ho-so');
        $this->render('auth/login', ['error' => $this->takeFlash(), 'csrf' => Csrf::token()]);
    }

    public function login(): void
    {
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('auth/login', ['error' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'csrf' => Csrf::token()]);
            return;
        }
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $key = hash('sha256', $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $attempt = $_SESSION['_login_attempts'][$key] ?? ['count' => 0, 'time' => time()];
        if (time() - (int) $attempt['time'] > 900) $attempt = ['count' => 0, 'time' => time()];
        if ((int) $attempt['count'] >= 5) {
            $this->render('auth/login', ['error' => 'Bạn đã thử quá nhiều lần. Vui lòng đợi 15 phút rồi thử lại.', 'csrf' => Csrf::token()]);
            return;
        }
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? (new User())->findByEmail($email) : null;
        if (!$user || $user['status'] !== 'active' || !password_verify((string) ($_POST['password'] ?? ''), $user['password'])) {
            $attempt['count']++;
            $_SESSION['_login_attempts'][$key] = $attempt;
            $this->render('auth/login', ['error' => 'Email hoặc mật khẩu chưa chính xác.', 'csrf' => Csrf::token()]);
            return;
        }
        unset($_SESSION['_login_attempts'][$key]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
        $this->redirect($user['role'] === 'admin' ? '/admin' : '/tai-khoan/ho-so');
    }

    public function registerForm(): void
    {
        if (isset($_SESSION['user'])) $this->redirect('/');
        $this->render('auth/register', ['error' => $this->takeFlash(), 'csrf' => Csrf::token()]);
    }

    public function register(): void
    {
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('auth/register', ['error' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'csrf' => Csrf::token()]);
            return;
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || strlen($password) > 200) {
            $this->render('auth/register', ['error' => 'Vui lòng kiểm tra họ tên, email và mật khẩu (tối thiểu 8 ký tự).', 'csrf' => Csrf::token()]);
            return;
        }
        $users = new User();
        if ($users->findByEmail($email)) {
            $this->render('auth/register', ['error' => 'Email này đã được sử dụng.', 'csrf' => Csrf::token()]);
            return;
        }
        try {
            $userId = $users->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        } catch (\PDOException) {
            $this->render('auth/register', ['error' => 'Không thể tạo tài khoản lúc này. Vui lòng thử lại.', 'csrf' => Csrf::token()]);
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'customer'];
        $this->redirect('/tai-khoan/ho-so');
    }

    public function profile(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('/dang-nhap');
        if (($_SESSION['user']['role'] ?? '') !== 'customer') {
            $this->redirect('/admin');
        }
        $user = (new User())->findProfile((int) $_SESSION['user']['id']);
        if (!$user) {
            unset($_SESSION['user']);
            $this->redirect('/dang-nhap');
        }
        $this->render('account/profile', [
            'user' => $user,
            'error' => $this->takeFlash(),
            'success' => $this->takeFlash('_success'),
            'csrf' => Csrf::token(),
        ]);
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('/dang-nhap');
        if (($_SESSION['user']['role'] ?? '') !== 'customer') $this->redirect('/admin');
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('account/profile', ['user' => array_merge($_SESSION['user'], $_POST), 'error' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'success' => null, 'csrf' => Csrf::token()]);
            return;
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || mb_strlen($phone) > 30 || mb_strlen($address) > 255 || ($phone !== '' && !preg_match('/^[0-9+().\s-]{7,30}$/', $phone))) {
            $this->render('account/profile', ['user' => array_merge($_SESSION['user'], ['name' => $name, 'phone' => $phone, 'address' => $address]), 'error' => 'Vui lòng kiểm tra họ tên, số điện thoại và địa chỉ.', 'success' => null, 'csrf' => Csrf::token()]);
            return;
        }
        (new User())->updateProfile((int) $_SESSION['user']['id'], $name, $phone, $address);
        $_SESSION['user']['name'] = $name;
        $_SESSION['_success'] = 'Thông tin hồ sơ đã được cập nhật.';
        $this->redirect('/');
    }

    public function logout(): void
    {
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn.';
            return;
        }
        unset($_SESSION['user']);
        session_regenerate_id(true);
        $this->redirect('/');
    }

    private function takeFlash(string $key = '_flash'): ?string
    {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return is_string($message) ? $message : null;
    }
}
