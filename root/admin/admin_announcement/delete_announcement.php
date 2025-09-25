<?php
// delete_announcement.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// Check if announcement ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../admin_announcements.php?error=Invalid announcement ID");
    exit;
}

$announcement_id = intval($_GET['id']);
$admin_id = 1; // This should come from your session: $_SESSION['user_id']

// Check if this is a confirmation request
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    try {
        // Verify the announcement exists and belongs to the user
        $stmt = $conn->prepare("SELECT author_id FROM announcements WHERE announcement_id = ?");
        $stmt->execute([$announcement_id]);
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$announcement) {
            header("Location: ../admin_announcements.php?error=Announcement not found");
            exit;
        }
        
        if ($announcement['author_id'] != $admin_id) {
            header("Location: ../admin_announcements.php?error=You can only delete your own announcements");
            exit;
        }
        
        // Delete the announcement
        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->execute([$announcement_id]);
        
        header("Location: ../admin_announcements.php?success=Announcement deleted successfully");
        exit;
        
    } catch (PDOException $e) {
        die("Error deleting announcement: " . $e->getMessage());
    }
}

// Fetch announcement details for confirmation page
$announcement = [];
try {
    $stmt = $conn->prepare("SELECT a.*, u.name as author_name FROM announcements a 
                           JOIN users u ON a.author_id = u.user_id 
                           WHERE a.announcement_id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$announcement) {
        header("Location: ../admin_announcements.php?error=Announcement not found");
        exit;
    }
    
    if ($announcement['author_id'] != $admin_id) {
        header("Location: ../admin_announcements.php?error=You can only delete your own announcements");
        exit;
    }
    
} catch (PDOException $e) {
    die("Error fetching announcement: " . $e->getMessage());
}

include('../../includes/header.php');
include('../../includes/admin_navbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Announcement - Janiking Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
    <div class="main-container">
        <main class="main-content">
            <div class="top-controls">
                <h1 class="page-title">Delete Announcement</h1>
                <a href="../admin_announcements.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Announcements
                </a>
            </div>

            <div class="profile-section">
                <div class="alert alert-danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Warning</h3>
                    <p>You are about to delete this announcement. This action cannot be undone.</p>
                </div>

                <div class="announcement-card">
                    <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                    <div class="announcement-meta">
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($announcement['author_name']); ?></p>
                        <p><strong>Created:</strong> <?php echo date("M d, Y \a\\t h:i A", strtotime($announcement['created_at'])); ?></p>
                        <?php if ($announcement['updated_at']): ?>
                            <p><strong>Last Updated:</strong> <?php echo date("M d, Y \a\\t h:i A", strtotime($announcement['updated_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="announcement-content">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                </div>

                <div class="confirmation-actions" style="margin-top: 30px;">
                    <a href="delete_announcement.php?id=<?php echo $announcement_id; ?>&confirm=yes" 
                       class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirm Delete
                    </a>
                    <a href="../admin_announcements.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../../assets/js/script_admin.js"></script>
</body>
</html>