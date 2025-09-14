<?php
session_start();
require_once "../../../db/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$parent_message_id = intval($_POST['parent_message_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($parent_message_id && $content !== '') {
    // Find the other participant
    $stmt = $conn->prepare("SELECT sender_id, receiver_id FROM messages WHERE message_id = ?");
    $stmt->execute([$parent_message_id]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($parent) {
        $receiver_id = ($parent['sender_id'] == $user_id) ? $parent['receiver_id'] : $parent['sender_id'];

        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, content, parent_message_id, is_announcement, status) 
            VALUES (?, ?, ?, ?, 0, 'Unread')
        ");
        $stmt->execute([$user_id, $receiver_id, $content, $parent_message_id]);
    }
}

header("Location: ../admin_announcements.php?tab=messages&thread=" . $parent_message_id);
exit;
