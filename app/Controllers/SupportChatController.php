<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\JsonResponse;
use App\Models\SupportChat;

final class SupportChatController extends Controller
{
    public function messages(): void
    {
        $userId = $this->customerId();
        if ($userId === null) {
            return;
        }

        JsonResponse::send((new SupportChat())->messagesForCustomer($userId));
    }

    public function unreadCount(): void
    {
        $userId = $this->customerId();
        if ($userId === null) {
            return;
        }

        JsonResponse::send(['unread_count' => (new SupportChat())->unreadCountForCustomer($userId)]);
    }

    public function send(): void
    {
        $userId = $this->customerId();
        if ($userId === null) {
            return;
        }
        if (!Csrf::valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            JsonResponse::send(['error' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 419);
        }

        $payload = $this->requestPayload();
        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '' || mb_strlen($message) > 2000) {
            JsonResponse::send(['error' => 'Tin nhắn phải có từ 1 đến 2.000 ký tự.'], 422);
        }

        (new SupportChat())->sendAsCustomer($userId, $message);
        JsonResponse::send(['sent' => true]);
    }

    private function customerId(): ?int
    {
        if (!isset($_SESSION['user'])) {
            JsonResponse::send(['error' => 'Vui lòng đăng nhập để chat với nhân viên hỗ trợ.'], 401);
        }
        if (($_SESSION['user']['role'] ?? '') !== 'customer' || !isset($_SESSION['user']['id'])) {
            JsonResponse::send(['error' => 'Bạn không có quyền sử dụng chức năng chat này.'], 403);
        }

        return (int) $_SESSION['user']['id'];
    }

    private function requestPayload(): array
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            JsonResponse::send(['error' => 'Dữ liệu gửi lên không hợp lệ.'], 400);
        }

        return $payload;
    }
}
