<?php
require_once '../includes/init.php';
require_auth();

$userId = get_user_id();
$userType = get_user_type();

$db = get_db_connection();

// Get creator profile for total earnings
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$creatorProfile = $profile;
$stmt->close();

// Get all conversations for this user
$stmt = db_prepare("
    SELECT
        m.id as message_id,
        m.sender_user_id,
        m.recipient_user_id,
        m.subject,
        m.message,
        m.is_read,
        m.created_at,
        CASE
            WHEN m.sender_user_id = ? THEN receiver.first_name
            ELSE sender.first_name
        END as other_first_name,
        CASE
            WHEN m.sender_user_id = ? THEN receiver.last_name
            ELSE sender.last_name
        END as other_last_name,
        CASE
            WHEN m.sender_user_id = ? THEN m.recipient_user_id
            ELSE m.sender_user_id
        END as other_user_id,
        (SELECT COUNT(*) FROM messages WHERE
            ((sender_user_id = m.sender_user_id AND recipient_user_id = m.recipient_user_id) OR
             (sender_user_id = m.recipient_user_id AND recipient_user_id = m.sender_user_id))
        ) as message_count
    FROM messages m
    JOIN users sender ON m.sender_user_id = sender.id
    JOIN users receiver ON m.recipient_user_id = receiver.id
    WHERE m.sender_user_id = ? OR m.recipient_user_id = ?
    GROUP BY
        CASE
            WHEN m.sender_user_id < m.recipient_user_id THEN CONCAT(m.sender_user_id, '-', m.recipient_user_id)
            ELSE CONCAT(m.recipient_user_id, '-', m.sender_user_id)
        END
    ORDER BY m.created_at DESC
");
$stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unread count
$stmt = db_prepare("SELECT COUNT(*) as unread_count FROM messages WHERE recipient_user_id = ? AND is_read = 0");
$stmt->bind_param('i', $userId);
$stmt->execute();
$unreadCount = $stmt->get_result()->fetch_assoc()['unread_count'];
$stmt->close();

$pageTitle = 'Messages - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <!-- Topbar -->
        <?php include_once '../includes/topbar.php'; ?>
        <!-- Header -->
        <div class="px-12 py-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold">Messages</h1>
                    <p class="text-gray-600 mt-1">
                        <?php if ($unreadCount > 0): ?>
                            You have <span class="font-semibold text-purple-600"><?= $unreadCount ?></span> unread
                            message<?= $unreadCount !== 1 ? 's' : '' ?>
                        <?php else: ?>
                            All caught up!
                        <?php endif; ?>
                    </p>
                </div>
                <button onclick="showComposeModal()"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-full font-semibold hover:opacity-90 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Message
                </button>
            </div>

            <?php if (empty($conversations)): ?>
                <!-- Empty State -->
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No messages yet</h3>
                    <p class="text-gray-600 mb-6">Start a conversation by sending your first message</p>
                    <button onclick="showComposeModal()"
                        class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition">
                        Send a Message
                    </button>
                </div>
            <?php else: ?>
                <!-- Conversations List -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($conversations as $conv): ?>
                            <?php
                            $isUnread = !$conv['is_read'] && $conv['recipient_user_id'] === $userId;
                            $isSent = $conv['sender_user_id'] === $userId;
                            ?>
                            <a href="thread.php?user_id=<?= $conv['other_user_id'] ?>"
                                class="block hover:bg-gray-50 transition p-6 <?= $isUnread ? 'bg-purple-50' : '' ?>">
                                <div class="flex items-start space-x-4">
                                    <!-- Avatar -->
                                    <div
                                        class="w-14 h-14 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                                        <?= strtoupper(substr($conv['other_first_name'], 0, 1) . substr($conv['other_last_name'], 0, 1)) ?>
                                    </div>

                                    <!-- Message Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <h3 class="text-lg font-semibold text-gray-900 truncate">
                                                <?= escape_output($conv['other_first_name'] . ' ' . $conv['other_last_name']) ?>
                                            </h3>
                                            <span class="text-sm text-gray-500 ml-2 flex-shrink-0">
                                                <?= time_ago($conv['created_at']) ?>
                                            </span>
                                        </div>

                                        <?php if ($conv['subject']): ?>
                                            <p class="text-sm font-medium text-gray-700 mb-1">
                                                <?= escape_output($conv['subject']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <p class="text-sm text-gray-600 truncate">
                                            <?php if ($isSent): ?>
                                                <span class="text-gray-500">You: </span>
                                            <?php endif; ?>
                                            <?= escape_output(truncate($conv['message'], 100)) ?>
                                        </p>

                                        <div class="flex items-center space-x-3 mt-2">
                                            <?php if ($isUnread): ?>
                                                <span class="px-2 py-1 bg-purple-600 text-white text-xs rounded-full font-semibold">
                                                    New
                                                </span>
                                            <?php endif; ?>
                                            <span class="text-xs text-gray-500">
                                                <?= $conv['message_count'] ?>
                                                message<?= $conv['message_count'] !== 1 ? 's' : '' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Arrow -->
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Compose Message Modal -->
    <div id="composeModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">New Message</h2>
                    <button onclick="hideComposeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="send-message.php" class="p-6">
                <?= csrf_field() ?>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To (User ID)</label>
                        <select name="receiver_id" id="receiver_id" required
                            class="w-full px-4 py-3 border bg-white text-gray-900 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="" disabled selected>Select a user</option>
                            <?php
                            // Fetch users to populate the dropdown
                            $stmt = db_prepare("SELECT id, first_name, last_name FROM users WHERE id != ?");
                            $stmt->bind_param('i', $userId);
                            $stmt->execute();
                            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();

                            foreach ($users as $user):
                            ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= escape_output($user['first_name'] . ' ' . $user['last_name']) ?> 
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- <input type="number" name="receiver_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter user ID">
                        <p class="text-xs text-gray-500 mt-1">Note: In production, this would be a searchable dropdown
                        </p> -->
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" maxlength="200"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter subject (optional)">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                        <textarea name="body" required rows="6"
                            class="w-full px-4 py-3 border text-gray-900 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                            placeholder="Type your message..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideComposeModal()"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-full font-semibold hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-full font-semibold hover:opacity-90 transition">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function showComposeModal() {
        document.getElementById('composeModal').classList.remove('hidden');
    }

    function hideComposeModal() {
        document.getElementById('composeModal').classList.add('hidden');
    }

    // Close modal on outside click
    document.getElementById('composeModal').addEventListener('click', function (e) {
        if (e.target === this) {
            hideComposeModal();
        }
    });
</script>

<?php require_once '../includes/footer2.php'; ?>