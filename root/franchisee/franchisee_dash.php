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
      <a class="action-card" href="franchisee_training">
        <i class="fas fa-graduation-cap"></i>
        <h3>Access Training</h3>
        <p>View courses</p>
      </a>
      <a class="action-card" href="franchisee_documents">
        <i class="fas fa-file-alt"></i>
        <h3>View Documents</h3>
        <p>Access your files</p>
      </a>
      <a class="action-card" href="franchisee_reports">
        <i class="fas fa-chart-line"></i>
        <h3>Generate Report</h3>
        <p>Create reports</p>
      </a>
      <a class="action-card" href="franchisee_products">
        <i class="fas fa-bag-shopping"></i>
        <h3>Buy Products</h3>
        <p>Browse products</p>
      </a>
    </div>

    <!-- Analytics -->
    <section class="analytics-section">
      <h2 class="section-title">Analytics Overview</h2>

      <div class="analytics-grid">
        <div class="analytics-card">
          <h3>Orders</h3>
          <div class="stat">
            <span class="stat-value">24</span>
            <span class="stat-change">+8%</span>
          </div>
          <p>From last month</p>
          <div class="chart-placeholder">Orders Chart</div>
          <a href="#" class="notification-action">View Details</a>
        </div>

        <div class="analytics-card">
          <h3>Training</h3>
          <div class="stat">
            <span class="stat-value">3</span>
            <span class="stat-change">+1</span>
          </div>
          <p>Upcoming sessions</p>
          <div class="chart-placeholder">Training Progress</div>
          <a href="#" class="notification-action">View Details</a>
        </div>

        <div class="analytics-card">
          <h3>Messages</h3>
          <div class="stat">
            <span class="stat-value">5</span>
            <span class="stat-change">+2</span>
          </div>
          <p>Unread</p>
          <div class="chart-placeholder">Inbox Activity</div>
          <a href="#" class="notification-action">Go to Inbox</a>
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
            <p>New eco-friendly chemical bundle now available.</p>
            <a href="#" class="notification-action">View Products <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>

        <div class="notification-card">
          <h3><i class="fas fa-graduation-cap"></i> Training Reminder</h3>
          <div class="notification-item">
            <p>“Advanced Cleaning Techniques” starts next week.</p>
            <a href="#" class="notification-action">View Training <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>

        <div class="notification-card">
          <h3><i class="fas fa-receipt"></i> Invoice Due</h3>
          <div class="notification-item">
            <p>Invoice #INV-2025-031 is due in 3 days.</p>
            <a href="#" class="notification-action">Pay Now <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

</body>
</html>
