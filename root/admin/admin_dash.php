<?php
// admin_dash.php

// Include database connection
require_once "../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// === DASHBOARD COUNTS ===
try {
    $booking_count = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) { $booking_count = 0; }

try {
    $product_count = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) { $product_count = 0; }

try {
    $user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) { $user_count = 0; }

try {
    $franchisee_count = $conn->query("SELECT COUNT(*) AS total FROM franchisees")->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) { $franchisee_count = 0; }

// === URGENT NOTIFICATIONS ===
$urgent_notifications = [];

try {
    // Pending appointments
    $pending_appointments = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'Pending'")->fetch(PDO::FETCH_ASSOC)['count'];
    if ($pending_appointments > 0) {
        $urgent_notifications[] = [
            'message' => "<strong>{$pending_appointments} pending appointments</strong> need approval",
            'link' => 'admin_manage_appointments.php',
            'action' => 'Review Now'
        ];
    }

    // Low stock
    $low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity BETWEEN 1 AND 30")->fetch(PDO::FETCH_ASSOC)['count'];
    if ($low_stock > 0) {
        $urgent_notifications[] = [
            'message' => "<strong>{$low_stock} inventory items</strong> are running low",
            'link' => 'admin_manage_inventory.php',
            'action' => 'Check Inventory'
        ];
    }

    // Out of stock
    $out_of_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0")->fetch(PDO::FETCH_ASSOC)['count'];
    if ($out_of_stock > 0) {
        $urgent_notifications[] = [
            'message' => "<strong>{$out_of_stock} products</strong> are out of stock",
            'link' => 'admin_manage_inventory.php',
            'action' => 'Restock Now'
        ];
    }
} catch (PDOException $e) {
    error_log("Urgent notifications query failed: " . $e->getMessage());
}

// === RECENT ACTIVITIES ===
$recent_activities = [];

try {
    // New users (last 7 days)
    $stmt = $conn->query("
        SELECT name, created_at 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $recent_activities[] = [
            'message' => "<strong>New user registration:</strong> " . htmlspecialchars($user['name']),
            'time' => time_elapsed_string($user['created_at'])
        ];
    }

    // New franchisees (last 7 days)
    $stmt = $conn->query("
        SELECT name, created_at 
        FROM franchisees 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fr) {
        $recent_activities[] = [
            'message' => "<strong>New franchisee created:</strong> " . htmlspecialchars($fr['name']),
            'time' => time_elapsed_string($fr['created_at'])
        ];
    }

    // New bookings (last 7 days)
    $stmt = $conn->query("
        SELECT first_name, last_name, created_at 
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $booking) {
        $recent_activities[] = [
            'message' => "<strong>New booking created:</strong> " . htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']),
            'time' => time_elapsed_string($booking['created_at'])
        ];
    }

    // New announcements (last 7 days)
    $stmt = $conn->query("
        SELECT title, created_at 
        FROM announcements 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $announcement) {
        $recent_activities[] = [
            'message' => "<strong>New announcement:</strong> " . htmlspecialchars($announcement['title']),
            'time' => time_elapsed_string($announcement['created_at'])
        ];
    }

} catch (PDOException $e) {
    error_log("Recent activities query failed: " . $e->getMessage());
}

// === Helper: Time Elapsed ===
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k.' '.$v.($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string).' ago' : 'just now';
}

include('../includes/header.php');
include('../includes/admin_navbar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>

<div class="main-container">
  <main class="main-content">
    <!-- Welcome -->
    <section class="welcome-section">
      <h1>Welcome back, Admin User</h1>
      <p>Here's what's happening in your portal today</p>
    </section>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <div class="action-card">
        <a href="admin_appointment/create_booking.php">
          <i class="fas fa-calendar-plus"></i>
          <h3>New Appointment</h3>
          <p>Schedule a new appointment</p>
        </a>
      </div>
      <div class="action-card">
        <a href="admin_inventory/create_product.php">
          <i class="fas fa-cube"></i>
          <h3>Add Inventory</h3>
          <p>Add new products to inventory</p>
        </a>
      </div>
      <div class="action-card">
        <a href="admin_manage_user/manage_user_add.php">
          <i class="fas fa-user-plus"></i>
          <h3>Add Users</h3>
          <p>Create new user accounts</p>
        </a>
      </div>
      <div class="action-card">
        <a href="admin_announcements.php">
          <i class="fas fa-bullhorn"></i>
          <h3>Send Announcements</h3>
          <p>Send messages to users</p>
        </a>
      </div>
      <div class="action-card">
        <a href="admin_reports.php">
          <i class="fas fa-chart-pie"></i>
          <h3>Generate Reports</h3>
          <p>Create performance reports</p>
        </a>
      </div>
    </div>

    <!-- Analytics -->
    <section class="analytics-section">
      <h2 class="section-title">Analytics Overview</h2>
      <div class="analytics-grid">
        <div class="analytics-card">
          <h3>Bookings</h3>
          <div class="stat">
            <span class="stat-value"><?php echo $booking_count; ?></span>
          </div>
          <p>Total bookings</p>
        </div>

        <div class="analytics-card">
          <h3>Products</h3>
          <div class="stat">
            <span class="stat-value"><?php echo $product_count; ?></span>
          </div>
          <p>Total products</p>
        </div>

        <div class="analytics-card">
          <h3>Users</h3>
          <div class="stat">
            <span class="stat-value"><?php echo $user_count; ?></span>
          </div>
          <p>Total users</p>
        </div>

        <div class="analytics-card">
          <h3>Franchisees</h3>
          <div class="stat">
            <span class="stat-value"><?php echo $franchisee_count; ?></span>
          </div>
          <p>Total franchisees</p>
        </div>
      </div>
    </section>

    <!-- Notifications -->
    <section class="notifications-section">
      <h2 class="section-title">Notifications</h2>
      <div class="notifications-grid">
        <!-- Urgent Actions -->
        <div class="notification-card">
          <h3><i class="fas fa-exclamation-circle"></i> Urgent Actions</h3>
          <?php if (!empty($urgent_notifications)): ?>
            <?php foreach ($urgent_notifications as $notification): ?>
              <div class="notification-item">
                <p><?php echo $notification['message']; ?></p>
                <a href="<?php echo $notification['link']; ?>" class="notification-action">
                  <?php echo $notification['action']; ?>
                </a>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="notification-item">
              <p>No urgent actions needed at this time.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="notification-card">
          <h3><i class="fas fa-bell"></i> Recent Activity</h3>
          <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
              <div class="notification-item">
                <p><?php echo $activity['message']; ?></p>
                <span><?php echo $activity['time']; ?></span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="notification-item">
              <p>No recent activities.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
</div>

<script src="../assets/js/script_admin.js"></script>
</body>
</html>
