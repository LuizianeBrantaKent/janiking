
<?php
// franchisee-dash.php
// Reuses ./includes/header.php (shared header) and ./includes/franchisee_navbar.php (left nav)

$pageTitle = 'Franchisee Dashboard';

if (session_status() === PHP_SESSION_NONE) session_start();

// normalize: if login.php set user_id, treat it as franchisee_id
if (!isset($_SESSION['franchisee_id']) && isset($_SESSION['user_id'])) {
    $_SESSION['franchisee_id'] = $_SESSION['user_id'];
}

// make sure the franchisee is logged in
$currentFranchiseeId = $_SESSION['franchisee_id'] ?? null;
if (!$currentFranchiseeId) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../db/config.php'; // gives $conn
$pdo = $conn;

// fetch franchisee details
$stmt = $pdo->prepare("SELECT business_name, email FROM franchisees WHERE franchisee_id = :fid LIMIT 1");
$stmt->execute([':fid' => $currentFranchiseeId]);
$fr = $stmt->fetch();

// Document Analytics
$docCount  = 0;
$lastTitle = null;

try {
    if ($franchiseeId) {
        // Count documents for this franchisee
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM documents WHERE franchisee_id = :fid");
        $stmt->execute([':fid' => $franchiseeId]);
        $docCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

        // Latest title for this franchisee (by created_at, fallback by id)
        $stmt = $pdo->prepare("
            SELECT title 
            FROM documents 
            WHERE franchisee_id = :fid 
            ORDER BY created_at DESC, documents_id DESC 
            LIMIT 1
        ");
        $stmt->execute([':fid' => $franchiseeId]);
        $lastTitle = $stmt->fetchColumn() ?: null;
    } else {
        // Fallback: show global stats if no franchisee in session
        $docCount = (int)$pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $stmt = $pdo->query("SELECT title FROM documents ORDER BY created_at DESC, documents_id DESC LIMIT 1");
        $lastTitle = $stmt->fetchColumn() ?: null;
    }
} catch (Throwable $e) {
    // Optional: log error; keep UI graceful
    // error_log($e->getMessage());
}

// Training Analytics

$trainingCount  = 0;
$lastTraining   = null;

try {
    if ($franchiseeId) {
        // Count trainings for this franchisee
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM training WHERE franchisee_id = :fid");
        $stmt->execute([':fid' => $franchiseeId]);
        $trainingCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

        // Latest training title
        $stmt = $pdo->prepare("
            SELECT title 
            FROM training 
            WHERE franchisee_id = :fid 
            ORDER BY created_at DESC, training_id DESC 
            LIMIT 1
        ");
        $stmt->execute([':fid' => $franchiseeId]);
        $lastTraining = $stmt->fetchColumn() ?: null;
    } else {
        // Global fallback
        $trainingCount = (int)$pdo->query("SELECT COUNT(*) FROM training")->fetchColumn();
        $stmt = $pdo->query("SELECT title FROM training ORDER BY created_at DESC, training_id DESC LIMIT 1");
        $lastTraining = $stmt->fetchColumn() ?: null;
    }
} catch (Throwable $e) {
    // error_log($e->getMessage());
}

// Messages Analytics

$unreadCount = 0;
$lastMessage = null;

try {
    if ($franchiseeId) {
        // Count unread messages for this franchisee
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS c
            FROM message_recipients r
            JOIN messages m ON m.message_id = r.message_id
            WHERE r.receiver_type = 'Franchisee'
              AND r.receiver_ref_id = :fid
              AND r.status = 'Unread'
        ");
        $stmt->execute([':fid' => $franchiseeId]);
        $unreadCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

        // Get subject of the latest message
        $stmt = $pdo->prepare("
            SELECT m.subject
            FROM messages m
            JOIN message_recipients r ON r.message_id = m.message_id
            WHERE r.receiver_type = 'Franchisee'
              AND r.receiver_ref_id = :fid
            ORDER BY m.sent_at DESC, m.message_id DESC
            LIMIT 1
        ");
        $stmt->execute([':fid' => $franchiseeId]);
        $lastMessage = $stmt->fetchColumn() ?: null;
    } else {
        // Fallback for admin/global view
        $unreadCount = (int)$pdo->query("
            SELECT COUNT(*) 
            FROM message_recipients 
            WHERE status = 'Unread'
        ")->fetchColumn();

        $stmt = $pdo->query("
            SELECT subject 
            FROM messages 
            ORDER BY sent_at DESC, message_id DESC 
            LIMIT 1
        ");
        $lastMessage = $stmt->fetchColumn() ?: null;
    }
} catch (Throwable $e) {
    // error_log($e->getMessage());
}

// Products Notification

$newProduct = null;

try {
    $stmt = $pdo->query("
        SELECT name 
        FROM products 
        ORDER BY product_id DESC 
        LIMIT 1
    ");
    $lastProduct = $stmt->fetchColumn() ?: null;
} catch (Throwable $e) {
    // error_log($e->getMessage());
}


// Training Notification

$lastTrainingTitle = null;

try {
    $stmt = $pdo->query("
        SELECT title 
        FROM training 
        ORDER BY created_at DESC, training_id DESC 
        LIMIT 1
    ");
    $lastTrainingTitle = $stmt->fetchColumn() ?: null;
} catch (Throwable $e) {
    // error_log($e->getMessage());
}

?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Franchisee Dashboard</title>
  <link rel="stylesheet" href="../assets/css/franchisee_portal.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
  <script src="/../assets/js/franchisee.js" defer></script>
  <img src="/../assets/images/logo.png" alt="JaniKing Logo" class="logo-img">
</head>
<body>


  <!-- Header & Navbar includes (relative to /franchisee) -->
  <?php require_once __DIR__ . '/../includes/franchisee_header.php'; ?>
  <?php require_once __DIR__ . '/../includes/franchisee_navbar.php'; ?>

?>

<div class="main-container">
  <main class="main-content">
    <!-- Welcome -->
    <section class="welcome-section">
      <h1 class="section-title">
  Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!
</h1>
      <p>Here’s what’s happening in your portal today.</p>
    </section>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <a class="action-card" href="franchisee_messaging.php">
        <i class="fas fa-envelope"></i>
        <h3>New Message</h3>
        <p>Compose/Read new messages</p>
      </a>
      <a class="action-card" href="franchisee_training.php">
        <i class="fas fa-graduation-cap"></i>
        <h3>Access Training</h3>
        <p>View courses</p>
      </a>
      <a class="action-card" href="franchisee_documents.php">
        <i class="fas fa-file-alt"></i>
        <h3>View Documents</h3>
        <p>Access your files</p>
      </a>
      <a class="action-card" href="franchisee_products.php">
        <i class="fas fa-bag-shopping"></i>
        <h3>Buy Products</h3>
        <p>Browse products</p>
      </a>
      <a class="action-card" href="franchisee_profile.php">
        <i class="fas fa-user-gear"></i>
        <h3>Edit your Profile</h3>
        <p>Change Password</p>
      </a>
    </div>

    <!-- Analytics -->
    <section class="analytics-section">
      <h2 class="section-title">Analytics Overview</h2>

      <div class="analytics-grid">
        <div class="analytics-card">
          <h3>Documents</h3>
          <div class="stat">
            <span class="stat-value"><?= (int)$docCount ?></span>
    </div>
          <p>Recently Added</p>
          <div class="chart-placeholder">
      <?= htmlspecialchars($lastTitle ?: 'No documents yet') ?>
    </div>
          <a href="franchisee_documents.php" class="notification-action">View Details</a>
        </div>

        <div class="analytics-card">
          <h3>Training</h3>
          <div class="stat">
            <span class="stat-value"><?= (int)$trainingCount ?></span>
          </div>
          <p>Recently Added</p>
          <div class="chart-placeholder"><?= htmlspecialchars($lastTraining ?: 'No training added yet') ?></div>
          <a href="franchisee_training.php" class="notification-action">View Details</a>
        </div>

        <div class="analytics-card">
          <h3>Messages</h3>
          <div class="stat">
            <span class="stat-value"><?= (int)$unreadCount ?></span>
          </div>
          <p>Received</p>
          <div class="chart-placeholder"><?= htmlspecialchars($lastMessage ?: 'No messages yet') ?></div>
          <a href="franchisee_messaging.php" class="notification-action">Go to Inbox</a>
        </div>
      </div>
    </section>

    <!-- Notifications -->
    <section class="notifications-section">
      <h2 class="section-title">Notifications</h2>

      <div class="notifications-grid">
        <div class="notification-card">
          <h3><i class="fa-solid fa-bag-shopping"></i> Product Launch</h3>
          <div class="notification-item">
            <p><?= $lastProduct ? "New " . htmlspecialchars($lastProduct) . " now available." : "No new products yet." ?></p>
            <a href="franchisee_products.php" class="notification-action">View Products <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>

        <div class="notification-card">
          <h3><i class="fas fa-graduation-cap"></i> Training Reminder</h3>
          <div class="notification-item">
            <p><?php if ($lastTrainingTitle): ?>
        Have a read on the “<?= htmlspecialchars($lastTrainingTitle) ?>” training.
      <?php else: ?>
        No training modules available yet.
      <?php endif; ?></p>
            <a href="franchisee_training.php" class="notification-action">View Training <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>

      </div>
    </section>
  </main>
</div>

</body>
</html>
