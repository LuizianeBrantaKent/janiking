<?php
// edit_announcement.php
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

// Fetch the announcement
$announcement = [];
try {
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcement_id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$announcement) {
        header("Location: ../admin_announcements.php?error=Announcement not found");
        exit;
    }
    
    // Check if user owns this announcement
    if ($announcement['author_id'] != $admin_id) {
        header("Location: ../admin_announcements.php?error=You can only edit your own announcements");
        exit;
    }
    
} catch (PDOException $e) {
    die("Error fetching announcement: " . $e->getMessage());
}

// Handle form submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    try {
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, updated_at = NOW() WHERE announcement_id = ?");
        $stmt->execute([$title, $content, $announcement_id]);
        
        $msg = "Announcement updated successfully!";
        // Refresh the announcement data
        $announcement['title'] = $title;
        $announcement['content'] = $content;
        
    } catch (PDOException $e) {
        $msg = "Error updating announcement: " . $e->getMessage();
    }
}

include('../../includes/header.php');
include('../../includes/admin_navbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement - Janiking Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
    <div class="main-container">
        <main class="main-content">
            <div class="top-controls">
                <h1 class="page-title">Edit Announcement</h1>
                <a href="../admin_announcements.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Announcements
                </a>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?php echo strpos($msg, 'Error') === false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="profile-section">
                <form method="post">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required 
                               value="<?php echo htmlspecialchars($announcement['title']); ?>" 
                               placeholder="Enter announcement title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea name="content" id="content" class="form-control" rows="6" required 
                                  placeholder="Enter announcement content"><?php echo htmlspecialchars($announcement['content']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Announcement
                        </button>
                        <a href="../admin_announcements.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
                
                <div class="announcement-meta" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <p><strong>Created:</strong> <?php echo date("M d, Y \a\\t h:i A", strtotime($announcement['created_at'])); ?></p>
                    <?php if ($announcement['updated_at']): ?>
                        <p><strong>Last Updated:</strong> <?php echo date("M d, Y \a\\t h:i A", strtotime($announcement['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../../assets/js/script_admin.js"></script>
</body>
</html>