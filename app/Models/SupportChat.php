<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SupportChat
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function messagesForCustomer(int $userId): array
    {
        $conversation = $this->conversationForUser($userId);
        if ($conversation === null) {
            return ['conversation_id' => null, 'messages' => []];
        }

        $this->markRead((int) $conversation['id'], 'admin');

        return [
            'conversation_id' => (int) $conversation['id'],
            'messages' => $this->messages((int) $conversation['id']),
        ];
    }

    public function unreadCountForCustomer(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM support_chat_messages m
             INNER JOIN support_chat_conversations c ON c.id = m.conversation_id
             WHERE c.user_id = :user_id AND m.sender_role = :sender_role AND m.is_read = 0'
        );
        $stmt->execute(['user_id' => $userId, 'sender_role' => 'admin']);

        return (int) $stmt->fetchColumn();
    }

    public function sendAsCustomer(int $userId, string $message): int
    {
        return $this->insertMessage($this->findOrCreateConversation($userId), 'customer', $message);
    }

    public function conversationsForAdmin(): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.user_id, u.name AS customer_name, u.phone AS customer_phone,
                    u.email AS customer_email,
                    c.updated_at,
                    (SELECT m.message FROM support_chat_messages m
                     WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS latest_message,
                    (SELECT m.created_at FROM support_chat_messages m
                     WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS latest_message_at,
                    (SELECT COUNT(*) FROM support_chat_messages m
                     WHERE m.conversation_id = c.id AND m.sender_role = :customer_role AND m.is_read = 0) AS unread_count
             FROM support_chat_conversations c
             INNER JOIN users u ON u.id = c.user_id
             ORDER BY COALESCE(c.updated_at, c.created_at) DESC, c.id DESC'
        );
        $stmt->execute(['customer_role' => 'customer']);

        return $stmt->fetchAll();
    }

    public function messagesForAdmin(int $conversationId): ?array
    {
        if (!$this->conversationExists($conversationId)) {
            return null;
        }
        $this->markRead($conversationId, 'customer');

        return $this->messages($conversationId);
    }

    public function sendAsAdmin(int $conversationId, string $message): bool
    {
        if (!$this->conversationExists($conversationId)) {
            return false;
        }
        $this->insertMessage($conversationId, 'admin', $message);

        return true;
    }

    private function conversationForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM support_chat_conversations WHERE user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch() ?: null;
    }

    private function findOrCreateConversation(int $userId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO support_chat_conversations (user_id)
             VALUES (:user_id)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $this->db->lastInsertId();
    }

    private function insertMessage(int $conversationId, string $senderRole, string $message): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO support_chat_messages (conversation_id, sender_role, message)
             VALUES (:conversation_id, :sender_role, :message)'
        );
        $stmt->execute([
            'conversation_id' => $conversationId,
            'sender_role' => $senderRole,
            'message' => $message,
        ]);
        $messageId = (int) $this->db->lastInsertId();
        $this->db->prepare(
            'UPDATE support_chat_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        )->execute(['id' => $conversationId]);

        return $messageId;
    }

    private function messages(int $conversationId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, sender_role, message, created_at
             FROM support_chat_messages
             WHERE conversation_id = :conversation_id
             ORDER BY id ASC'
        );
        $stmt->execute(['conversation_id' => $conversationId]);

        return $stmt->fetchAll();
    }

    private function markRead(int $conversationId, string $senderRole): void
    {
        $stmt = $this->db->prepare(
            'UPDATE support_chat_messages
             SET is_read = 1
             WHERE conversation_id = :conversation_id AND sender_role = :sender_role AND is_read = 0'
        );
        $stmt->execute([
            'conversation_id' => $conversationId,
            'sender_role' => $senderRole,
        ]);
    }

    private function conversationExists(int $conversationId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM support_chat_conversations WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $conversationId]);

        return (bool) $stmt->fetchColumn();
    }
}
