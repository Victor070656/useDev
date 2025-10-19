<?php
require_once '../includes/init.php';
require_auth();

$userId = get_user_id();
$otherUserId = get_query('user_id');

if (!$otherUserId) {
    redirect('/messages/inbox.php');
    exit;
}

$db = get_db_connection();

// Get other user info
$stmt = db_prepare("SELECT id, first_name, last_name, user_type, email FROM users WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $otherUserId);
$stmt->execute();
$otherUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$otherUser) {
    set_flash('error', 'User not found');
    redirect('/messages/inbox.php');
    exit;
}

// Mark all messages from this user as read
$stmt = db_prepare("UPDATE messages SET is_read = 1 WHERE sender_user_id = ? AND recipient_user_id = ?");
$stmt->bind_param('ii', $otherUserId, $userId);
$stmt->execute();
$stmt->close();

// Get all messages between these two users
$stmt = db_prepare("
    SELECT
        m.id,
        m.sender_user_id,
        m.recipient_user_id,
        m.subject,
        m.message as body,
        m.is_read,
        m.created_at,
        sender.first_name as sender_first_name,
        sender.last_name as sender_last_name
    FROM messages m
    JOIN users sender ON m.sender_user_id = sender.id
    WHERE (m.sender_user_id = ? AND m.recipient_user_id = ?)
       OR (m.sender_user_id = ? AND m.recipient_user_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->bind_param('iiii', $userId, $otherUserId, $otherUserId, $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Chat with ' . $otherUser['first_name'] . ' ' . $otherUser['last_name'] . ' - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="inbox.php" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($otherUser['first_name'], 0, 1) . substr($otherUser['last_name'], 0, 1)) ?>
                </div>

                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        <?= escape_output($otherUser['first_name'] . ' ' . $otherUser['last_name']) ?>
                    </h1>
                    <p class="text-sm text-gray-500">
                        <?= ucfirst($otherUser['user_type']) ?>
                    </p>
                </div>
            </div>

            <?php if ($otherUser['user_type'] === 'creator'): ?>
                <a href="../creator-profile.php?id=<?= $otherUser['id'] ?>"
                    class="px-4 py-2 border border-purple-600 text-purple-600 rounded-full text-sm font-semibold hover:bg-purple-50 transition">
                    View Profile
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Messages Thread -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col" style="height: 600px;">
        <!-- Messages Area -->
        <div id="messagesArea" class="flex-1 overflow-y-auto p-6 space-y-4">
            <?php if (empty($messages)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-gray-500">No messages yet. Start the conversation!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isSent = $msg['sender_user_id'] === $userId; ?>

                    <div class="flex <?= $isSent ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-xl <?= $isSent ? 'order-2' : 'order-1' ?>">
                            <?php if ($msg['subject'] && ($messages[0]['id'] === $msg['id'])): ?>
                                <div class="text-xs font-semibold text-gray-500 mb-1 <?= $isSent ? 'text-right' : 'text-left' ?>">
                                    Subject: <?= escape_output($msg['subject']) ?>
                                </div>
                            <?php endif; ?>

                            <div
                                class="<?= $isSent ? 'bg-gradient-to-r from-purple-600 to-purple-800 text-white' : 'bg-gray-100 text-gray-900' ?> rounded-2xl px-4 py-3">
                                <p class="text-sm whitespace-pre-line break-words"><?= escape_output($msg['body']) ?></p>
                            </div>

                            <div class="text-xs text-gray-500 mt-1 <?= $isSent ? 'text-right' : 'text-left' ?>">
                                <?= format_datetime($msg['created_at']) ?>
                                <?php if ($isSent): ?>
                                    <?php if ($msg['is_read']): ?>
                                        <span class="ml-1">• Read</span>
                                    <?php else: ?>
                                        <span class="ml-1">• Sent</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Message Input Area -->
        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <form method="POST" action="send-message.php" class="flex items-end space-x-3">
                <?= csrf_field() ?>
                <input type="hidden" name="receiver_id" value="<?= $otherUserId ?>">
                <input type="hidden" name="redirect_to_thread" value="1">

                <div class="flex-1">
                    <textarea name="body" required rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                        placeholder="Type your message..."
                        onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.form.submit(); }"></textarea>
                </div>

                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-xl font-semibold hover:opacity-90 transition flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2">Press Enter to send, Shift+Enter for new line</p>
        </div>
    </div>
</div>

<script>
    // Auto-scroll to bottom of messages
    const messagesArea = document.getElementById('messagesArea');
    messagesArea.scrollTop = messagesArea.scrollHeight;

    // Focus on textarea on load
    document.querySelector('textarea[name="body"]').focus();
</script>

<?php require_once '../includes/footer.php'; ?>