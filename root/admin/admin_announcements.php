<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../../db/config.php";
include('../includes/header.php');
include('../includes/admin_navbar.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Unknown';
$msg = "";

// Handle new announcement/message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $content = htmlspecialchars(trim($_POST['content'] ?? ''));
    $recipient_type = $_POST['recipient_type'] ?? '';
    $recipient_email = trim($_POST['recipient_email'] ?? '');

    if (empty($content)) {
        $msg = "Message content cannot be empty.";
    } else {
        try {
            if (in_array($recipient_type, ['all', 'all_staff', 'all_franchisee'])) {
                if ($role !== 'Admin') {
                    throw new Exception("Only admins can send announcements.");
                }
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, author_id) VALUES (?, ?, ?)");
                $stmt->execute(['System Announcement', $content, $user_id]);
                $msg = "Announcement sent successfully!";
            } elseif ($recipient_type === 'direct') {
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? 
                                        UNION SELECT franchisee_id AS user_id FROM franchisees WHERE email = ? LIMIT 1");
                $stmt->execute([$recipient_email, $recipient_email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $recipient_id = $result['user_id'] ?? 0;

                if ($recipient_id == 0) throw new Exception("Recipient not found.");
                if ($recipient_id == $user_id) throw new Exception("Cannot send message to yourself.");

                $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, is_announcement, status) 
                                        VALUES (?, ?, ?, 0, 'Unread')");
                $stmt->execute([$user_id, $recipient_id, $content]);
                $msg = "Message sent successfully to $recipient_email!";
            } else {
                throw new Exception("Invalid recipient type.");
            }
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Announcements
$announcements = [];
$stmt = $conn->query("SELECT a.*, u.name AS author_name 
                      FROM announcements a 
                      LEFT JOIN users u ON a.author_id = u.user_id 
                      ORDER BY a.created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Direct Messages
$messages = [];
$stmt = $conn->prepare("SELECT m.*, u.name AS sender_name 
                        FROM messages m 
                        LEFT JOIN users u ON m.sender_id = u.user_id 
                        WHERE m.receiver_id = ? OR m.sender_id = ? 
                        ORDER BY m.sent_at DESC");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janiking - Communication Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style_admin.css">
    <!-- jQuery UI CSS for autocomplete styling -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <!-- jQuery (required for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- jQuery UI JS for autocomplete -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <style>
        .nav-tabs { display: flex; margin-bottom: 10px; }
        .nav-tab { padding: 10px; cursor: pointer; background: #eee; margin-right: 5px; border-radius: 5px; }
        .nav-tab.active { background: #ccc; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .delete-btn { color: red; font-size: 0.9em; margin-left: 10px; }
        /* Basic autocomplete dropdown styling */
        .ui-autocomplete { max-height: 200px; overflow-y: auto; overflow-x: hidden; padding: 0; border: 1px solid #ccc; background: white; }
        .ui-menu-item { padding: 8px; cursor: pointer; }
        .ui-menu-item:hover { background: #f0f0f0; }
    </style>
</head>
<body>
<div class="main-container">
    <main class="main-content">
        <h1 class="page-title">Communication Center</h1>
        <p class="page-subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?> (<?php echo htmlspecialchars($role); ?>)</p>

        <!-- Send Message/Announcement -->
        <div class="profile-section">
            <h2 class="section-title"><i class="fas fa-envelope"></i> Send Message</h2>
            <?php if ($msg) echo "<div class='alert alert-" . (strpos($msg, 'Error') === false ? 'success' : 'danger') . "'>$msg</div>"; ?>
            <form method="post">
                <input type="hidden" name="action" value="send">
                <div class="form-group">
                    <label>Send To</label>
                    <select name="recipient_type" class="form-control" onchange="toggleRecipient(this)" required>
                        <option value="">-- Select Recipient --</option>
                        <option value="direct">Direct Message</option>
                        <?php if ($role === 'Admin'): ?>
                            <option value="all">All Users</option>
                            <option value="all_staff">All Staff</option>
                            <option value="all_franchisee">All Franchisees</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" id="recipient_select" style="display:none;">
                    <label for="recipient_email">Recipient Email</label>
                    <input type="email" name="recipient_email" id="recipient_email" class="form-control" placeholder="Type email to select recipient">
                </div>
                <div class="form-group">
                    <label for="content">Message</label>
                    <textarea name="content" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>

        <!-- Tabs -->
        <div class="nav-tabs">
            <div class="nav-tab active" onclick="showTab('messages')">Messages</div>
            <div class="nav-tab" onclick="showTab('announcements')">Announcements</div>
        </div>

       <!-- Messages -->
<div id="messages-tab" class="tab-content active">
    <?php if (isset($_GET['thread'])): ?>
        <?php
        // Show full thread view
        $thread_id = intval($_GET['thread']);

        // Fetch all messages in this thread
        $stmt = $conn->prepare("
            SELECT m.*, u.name AS sender_name 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.user_id 
            WHERE m.message_id = ? OR m.parent_message_id = ?
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([$thread_id, $thread_id]);
        $thread_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($thread_messages)): ?>
            <div class="alert alert-warning">Thread not found.</div>
        <?php else: ?>
            <h2>Conversation</h2>
            <div class="thread-view">
                <?php foreach ($thread_messages as $tm): ?>
                    <div class="message-bubble <?php echo $tm['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <p><?php echo nl2br(htmlspecialchars($tm['content'])); ?></p>
                        <small><?php echo htmlspecialchars($tm['sender_name']); ?> - <?php echo date("M d, Y h:i A", strtotime($tm['sent_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Reply form -->
            <form method="post" action="admin_announcement/reply_message.php" class="reply-form">
                <input type="hidden" name="parent_message_id" value="<?php echo $thread_id; ?>">
                <textarea name="content" required placeholder="Type your reply..."></textarea>
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
        <?php endif; ?>

        <p><a href="admin_announcements.php?tab=messages">&larr; Back to messages</a></p>

    <?php else: ?>
        <?php
        // Fetch only root messages
        $stmt = $conn->prepare("
            SELECT m.*, u.name AS sender_name 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.user_id 
            WHERE (m.receiver_id = ? OR m.sender_id = ?) 
              AND m.parent_message_id IS NULL
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        $root_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($root_messages)): ?>
            <div class="alert alert-info">No messages yet.</div>
        <?php else: ?>
            <?php foreach ($root_messages as $m): ?>
                <div class="announcement-card <?php echo $m['status'] === 'Unread' ? 'unread' : ''; ?>">
                    <div class="announcement-header">
                        <a href="admin_announcements.php?tab=messages&thread=<?php echo $m['message_id']; ?>">
                            <h3>Message from <?php echo htmlspecialchars($m['sender_name'] ?? 'Unknown'); ?></h3>
                            <span><?php echo date("M d, Y h:i A", strtotime($m['sent_at'])); ?></span>
                        </a>
                    </div>
                    <div class="announcement-content"><?php echo nl2br(htmlspecialchars($m['content'])); ?></div>
                    <a href="admin_announcement/delete_message.php?id=<?php echo $m['message_id']; ?>" class="delete-btn">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>


        <!-- Announcements -->
<div id="announcements-tab" class="tab-content">
    <?php if (empty($announcements)): ?>
        <div class="alert alert-info">No announcements yet.</div>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
            <div class="announcement-card">
                <div class="announcement-header">
                    <h3>Announcement by <?php echo htmlspecialchars($a['author_name'] ?? 'Admin'); ?></h3>
                    <span><?php echo date("M d, Y h:i A", strtotime($a['created_at'])); ?></span>
                </div>
                <div class="announcement-content"><?php echo nl2br(htmlspecialchars($a['content'])); ?></div>
                <?php if ($role === 'Admin'): ?>
                    <!-- Use relative path like delete_message -->
                    <a href="admin_announcement/delete_announcement.php?id=<?php echo $a['announcement_id']; ?>" class="delete-btn">
                        <i class="fas fa-trash"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<script>
function toggleRecipient(select) {
    const recipientSelect = document.getElementById('recipient_select');
    if (select.value === 'direct') {
        recipientSelect.style.display = 'block';
        // Initialize autocomplete once the field is shown
        if (!recipientSelect.dataset.initialized) {
            $("#recipient_email").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "../ajax/search_user.php",  // Relative path from /admin/admin_announcement/ to /ajax/
                        type: "GET",
                        dataType: "json",
                        data: {
                            email: request.term  // Sends prefix like "jo"
                        },
                        success: function(data) {
                            response(data);  // data is array of {user_id, email, name}
                        },
                        error: function(xhr, status, error) {
                            console.error("Autocomplete error: " + error);
                            response([]);
                        }
                    });
                },
                minLength: 2,  // Start searching after 2 characters
                select: function(event, ui) {
                    // Set the selected email into the input
                    $("#recipient_email").val(ui.item.email);
                    return false;
                },
                focus: function(event, ui) {
                    // Optional: Show name in dropdown, but set email on select
                    return false;
                }
            });
            recipientSelect.dataset.initialized = 'true';  // Prevent re-initializing
        }
    } else {
        recipientSelect.style.display = 'none';
        $("#recipient_email").val("");  // Clear on hide
    }
}

function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(tab + '-tab').classList.add('active');
    document.querySelector('.nav-tab[onclick="showTab(\''+tab+'\')"]').classList.add('active');
}
</script>
</body>
</html>