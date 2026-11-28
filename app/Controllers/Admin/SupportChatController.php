<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\JsonResponse;
use App\Models\SupportChat;

final class SupportChatController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->render('admin/chat/index', [
            'title' => 'Chat hỗ trợ',
            'conversations' => (new SupportChat())->conversationsForAdmin(),
            'csrf' => Csrf::token(),
        ]);
    }

    public function conversations(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        JsonResponse::send(['conversations' => (new SupportChat())->conversationsForAdmin()]);
    }

    public function messages(array $params): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        $conversationId = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $messages = $conversationId === false
            ? null
            : (new SupportChat())->messagesForAdmin((int) $conversationId);
        if ($messages === null) {
            JsonResponse::send(['error' => 'Không tìm thấy cuộc trò chuyện.'], 404);
        }

        JsonResponse::send(['messages' => $messages]);
    }

    public function reply(array $params): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        if (!Csrf::valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            JsonResponse::send(['error' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 419);
        }

        $conversationId = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if ($conversationId === false || !is_array($payload)) {
            JsonResponse::send(['error' => 'Dữ liệu gửi lên không hợp lệ.'], 400);
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '' || mb_strlen($message) > 2000) {
            JsonResponse::send(['error' => 'Tin nhắn phải có từ 1 đến 2.000 ký tự.'], 422);
        }
        if (!(new SupportChat())->sendAsAdmin((int) $conversationId, $message)) {
            JsonResponse::send(['error' => 'Không tìm thấy cuộc trò chuyện.'], 404);
        }

        JsonResponse::send(['sent' => true]);
    }

    private function isAdmin(): bool
    {
        if (!isset($_SESSION['user'])) {
            JsonResponse::send(['error' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            JsonResponse::send(['error' => 'Bạn không có quyền truy cập chức năng này.'], 403);
        }

        return true;
    }
}
