<?php
declare(strict_types=1);

// staff_dash.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Normalise: if login.php set user_id, treat it as staff_id
if (!isset($_SESSION['staff_id']) && isset($_SESSION['user_id'])) {
    $_SESSION['staff_id'] = $_SESSION['user_id'];
}

// Require login
$currentStaffId = $_SESSION['staff_id'] ?? null;
if (!$currentStaffId) {
    header('Location: ../login.php');
    exit;
}

// DB (config provides $conn as PDO)
require_once __DIR__ . "/../../db/config.php"; // <-- adjust if your config path differs
/** @var PDO $conn */
$pdo = $conn;

// Utility escaper
if (!function_exists('e')) {
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// ---- Fetch staff profile ----
$staffStmt = $pdo->prepare("
    SELECT name, role
    FROM users
    WHERE user_id = :sid
      AND role = 'Staff'
    LIMIT 1
");
$staffStmt->execute([':sid' => $currentStaffId]);
$staff = $staffStmt->fetch() ?: ['name' => 'Staff', 'role' => 'Staff'];

// ---- Avatar fallback (no avatar column in users table) ----
$avatarPath = '/assets/images/default-avatar.png';

// ---- KPIs ----
$kpi = [
    'franchisees'       => (int)$pdo->query("SELECT COUNT(*) FROM franchisees")->fetchColumn(),
    'bookings_pending'  => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
];

// ---- Unread messages for this staff ----
// A message is unread if in message_recipients with status='Unread'
// where receiver_type='User' and receiver_ref_id = staff_id
// OR receiver_type='AllStaff' (broadcasts)
$unreadStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM message_recipients mr
    WHERE mr.status = 'Unread'
      AND (
        (mr.receiver_type = 'User' AND mr.receiver_ref_id = :sid)
        OR mr.receiver_type = 'AllStaff'
      )
");
$unreadStmt->execute([':sid' => $currentStaffId]);
$kpi['unread_msgs'] = (int)$unreadStmt->fetchColumn();

// ---- Recent announcements ----
// In your DB, announcements are stored in announcements table
$annStmt = $pdo->query("
    SELECT announcement_id, title, content, created_at
    FROM announcements
    WHERE recipient_type IN ('All','Staff')
    ORDER BY created_at DESC
    LIMIT 6
");
$recentAnnouncements = $annStmt->fetchAll();

// ---- Page title (used by header include) ----
$pageTitle = 'Staff Dashboard';

// Optional theming vars for header include
$logoPath = '/assets/images/logo.png';

// ---- Includes: shared header + left navbar ----
require_once __DIR__ . '/../includes/staff_header.php';
require_once __DIR__ . '/../includes/staff_navbar.php';
?>

<!-- Main container -->
<div class="main-container">
  <main class="main-content">
    <!-- Welcome -->
    <section class="welcome-section">
      <h1 class="section-title">Welcome back, <?= e($_SESSION['name'] ?? $staff['name']) ?>!</h1>
      <p class="mb-0" style="color:var(--muted)">Here’s a quick overview of what needs your attention.</p>
    </section>

    <!-- KPI Cards -->
    <section class="analytics-section" style="margin-top:1rem">
      <div class="analytics-grid">
        <div class="analytics-card">
          <h3><i class="fa-solid fa-users"></i> Active Franchisees</h3>
          <div class="stat">
            <span class="stat-value"><?= e((string)$kpi['franchisees']) ?></span>
          </div>
          <p>Total count in the network</p>
        </div>

        <div class="analytics-card">
          <h3><i class="fa-solid fa-calendar-check"></i> Pending Bookings</h3>
          <div class="stat">
            <span class="stat-value"><?= e((string)$kpi['bookings_pending']) ?></span>
          </div>
          <p>Awaiting confirmation</p>
        </div>

        <div class="analytics-card">
          <h3><i class="fa-solid fa-envelope-open-text"></i> Unread Messages</h3>
          <div class="stat">
            <span class="stat-value"><?= e((string)$kpi['unread_msgs']) ?></span>
          </div>
          <p>Direct + announcements</p>
        </div>
      </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions" style="margin-top:1.5rem">
      <a class="action-card" href="staff_announcements.php">
        <i class="fas fa-comments"></i>
        <h3>Communication</h3>
        <p>Read & send messages</p>
      </a>
      <a class="action-card" href="staff_reports.php">
        <i class="fas fa-chart-line"></i>
        <h3>Reports</h3>
        <p>Generate & export</p>
      </a>
      <a class="action-card" href="staff_documents.php">
        <i class="fas fa-folder-open"></i>
        <h3>Documents</h3>
        <p>Browse files</p>
      </a>
      <a class="action-card" href="staff_training.php">
        <i class="fas fa-graduation-cap"></i>
        <h3>Training</h3>
        <p>View sessions</p>
      </a>
      <a class="action-card" href="staff_profile.php">
        <i class="fas fa-user"></i>
        <h3>Profile / Settings</h3>
        <p>Manage account</p>
      </a>
    </section>

    <!-- Announcements -->
    <section class="notifications-section" style="margin-top:2rem">
<div class="cardx recent-announcements">
  <div class="card-header">
    <h2 class="section-title">Recent Announcements</h2>
  </div>
  <div class="card-body">
    <div class="accordion" id="inboxAccordion">

      <?php if ($recentAnnouncements): ?>
        <div class="notifications-grid">
          <?php foreach ($recentAnnouncements as $a): ?>
            <div class="notification-card">
              <h3><i class="fas fa-bullhorn"></i> <?= e($a['title'] ?: 'Announcement') ?></h3>
              <div class="notification-item">
                <p><?= e(mb_strimwidth((string)$a['content'], 0, 180, '…')) ?></p>
                <div class="meta" style="color:var(--muted)">
                  <?= e(date('Y-m-d H:i', strtotime((string)$a['created_at']))) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="meta" style="color:var(--muted)">No announcements yet.</p>
      <?php endif; ?>
    </section>
  </main>
</div>
    </div>
  </div>
</div>

