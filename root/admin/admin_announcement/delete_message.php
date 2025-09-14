<?php
session_start();
require_once "../../../db/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'User';

$message_id = intval($_GET['id'] ?? 0);
if (!$message_id) {
    header("Location: /admin/admin_announcements.php?error=Invalid message ID");
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE message_id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        header("Location: /admin/admin_announcements.php?error=Message not found");
        exit;
    }

    if ($message['sender_id'] != $user_id && $message['receiver_id'] != $user_id && $role !== 'Admin') {
        header("Location: /admin/admin_announcements.php?error=You don’t have permission to delete this message");
        exit;
    }

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        $stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$message_id]);
        header("Location: /admin/admin_announcements.php?success=Message deleted successfully");
        exit;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

include('../../includes/header.php');
include('../../includes/admin_navbar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Message - Janiking Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
<div class="main-container">
    <main class="main-content">
        <h1>Delete Message</h1>
        <div class="alert alert-danger">
            <p>Are you sure you want to delete this message? This cannot be undone.</p>
        </div>
        <div class="announcement-card">
            <strong>Message:</strong><br>
            <?php echo nl2br(htmlspecialchars($message['content'])); ?>
        </div>
        <div style="margin-top:20px;">
            <a href="delete_message.php?id=<?php echo $message_id; ?>&confirm=yes" class="btn btn-danger">Confirm Delete</a>
            <a href="/admin/admin_announcements.php" class="btn btn-secondary">Cancel</a>
        </div>
    </main>
</div>
</body>
</html>
