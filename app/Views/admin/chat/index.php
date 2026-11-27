<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Helpers/view.php';

$title = $title ?? 'Chat hỗ trợ';
$conversations = $conversations ?? [];
ob_start();
?>
<section class="admin-page"><div class="support-chat-admin" data-admin-chat
    data-threads-url="<?= e(url('/admin/chat/cuoc-tro-chuyen')) ?>"
    data-chat-url="<?= e(url('/admin/chat')) ?>"
    data-csrf="<?= e($csrf) ?>">
    <header class="admin-heading">
        <div><span class="eyebrow"><span></span> CHĂM SÓC KHÁCH HÀNG</span><h1>Chat hỗ trợ</h1><p>Tiếp nhận và phản hồi tin nhắn từ khách hàng.</p></div>
        <span class="admin-badge" data-chat-conversation-count><?= count($conversations) ?> cuộc trò chuyện</span>
    </header>
    <div class="support-chat-admin-grid">
        <aside class="support-chat-inbox" aria-label="Danh sách cuộc trò chuyện" data-chat-conversations>
            <?php foreach ($conversations as $conversation): ?>
                <button type="button" class="support-chat-conversation"
                    data-conversation-id="<?= (int) $conversation['id'] ?>"
                    aria-pressed="false">
                    <span class="support-chat-conversation-top"><strong><?= e($conversation['customer_name']) ?></strong>
                        <?php if ((int) $conversation['unread_count'] > 0): ?><span class="support-chat-unread"><?= (int) $conversation['unread_count'] ?></span><?php endif; ?>
                    </span>
                    <span class="support-chat-conversation-phone"><?= e(trim((string) ($conversation['customer_phone'] ?? '')) !== '' ? $conversation['customer_phone'] : 'Khách hàng chưa nhập số điện thoại') ?></span>
                    <span class="support-chat-conversation-email"><?= e($conversation['customer_email']) ?></span>
                    <span class="support-chat-conversation-preview"><?= e($conversation['latest_message'] ?? 'Chưa có tin nhắn') ?></span>
                    <time><?= !empty($conversation['latest_message_at']) ? e(date('d/m H:i', strtotime($conversation['latest_message_at']))) : '' ?></time>
                </button>
            <?php endforeach; ?>
            <?php if (!$conversations): ?><p class="support-chat-empty-inbox">Chưa có cuộc trò chuyện nào.</p><?php endif; ?>
        </aside>
        <section class="support-chat-admin-panel" aria-label="Nội dung trò chuyện">
            <header class="support-chat-admin-heading" data-chat-active-heading>Chọn cuộc trò chuyện để xem tin nhắn</header>
            <div class="support-chat-messages support-chat-admin-messages" data-chat-messages aria-live="polite">
                <p class="support-chat-state">Tin nhắn sẽ hiển thị tại đây.</p>
            </div>
            <p class="support-chat-error" data-chat-error role="status" hidden></p>
            <form class="support-chat-form" data-chat-form hidden>
                <label class="sr-only" for="admin-support-chat-input">Tin nhắn trả lời</label>
                <textarea id="admin-support-chat-input" name="message" rows="2" maxlength="2000" placeholder="Nhập nội dung trả lời..." required></textarea>
                <button type="submit">Gửi trả lời</button>
            </form>
        </section>
    </div>
</div></section>
<?php $content = ob_get_clean(); require dirname(__DIR__, 2) . '/layouts/main.php'; ?>
