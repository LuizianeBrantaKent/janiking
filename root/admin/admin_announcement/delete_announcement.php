<?php
session_start();
require_once "C:/xampp/db/config.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'User';

$announcement_id = intval($_GET['id'] ?? 0);
if (!$announcement_id) {
    header("Location: /admin/admin_announcements.php?error=Invalid announcement ID");
    exit;
}

try {
    // Fetch announcement
    $stmt = $conn->prepare("SELECT a.*, u.name as author_name FROM announcements a 
                            JOIN users u ON a.author_id = u.user_id 
                            WHERE a.announcement_id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        header("Location: /admin/admin_announcements.php?error=Announcement not found");
        exit;
    }

    // Only Admins can delete
    if ($role !== 'Admin') {
        header("Location: /admin/admin_announcements.php?error=Only admins can delete announcements");
        exit;
    }

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->execute([$announcement_id]);
        header("Location: /admin/admin_announcements.php?success=Announcement deleted successfully");
        exit;
    }

} catch (PDOException $e) {
    die("Error: ".$e->getMessage());
}

// Includes
include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
include($_SERVER['DOCUMENT_ROOT'].'/includes/admin_navbar.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Announcement - Janiking Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style_admin.css">
</head>
<body>
<div class="main-container">
    <main class="main-content">
        <h1>Delete Announcement</h1>
        <div class="alert alert-danger">
            <p>Are you sure you want to delete this announcement? This cannot be undone.</p>
        </div>
        <div class="announcement-card">
            <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
            <small>By <?php echo htmlspecialchars($announcement['author_name']); ?> on <?php echo $announcement['created_at']; ?></small>
        </div>
        <div style="margin-top:20px;">
            <a href="delete_announcement.php?id=<?php echo $announcement_id; ?>&confirm=yes" class="btn btn-danger">Confirm Delete</a>
            <a href="/admin/admin_announcements.php" class="btn btn-secondary">Cancel</a>
        </div>
    </main>
</div>
</body>
</html>
