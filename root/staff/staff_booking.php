<?php
// staff_booking.php — Staff can create and reschedule appointments only
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Staff validation using your users table schema
$currentUserId = $_SESSION['user_id'] ?? null;
$currentRole   = $_SESSION['role'] ?? null;

if (!$currentUserId || $currentRole !== 'Staff') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../db/config.php';
/** @var PDO $conn */
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Escape output for safe HTML
if (!function_exists('e')) {
    function e(mixed $s): string {
        if ($s === null) return '';
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Trim & sanitize strings (e.g., from forms)
if (!function_exists('sanitize')) {
    function sanitize(?string $s): string {
        return trim(filter_var((string)$s, FILTER_SANITIZE_STRING));
    }
}

// Validate integers (safe cast)
if (!function_exists('toInt')) {
    function toInt($value): int {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
}

// Validate emails
if (!function_exists('isValidEmail')) {
    function isValidEmail(?string $email): bool {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

// Validate dates (format example: Y-m-d H:i:s)
if (!function_exists('isValidDate')) {
    function isValidDate(?string $date, string $format = 'Y-m-d H:i:s'): bool {
        if (!$date) return false;
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>JaniKing – Staff Appointments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/staff.css">
  <script src="../assets/js/staff.js" defer></script>
</head>
<body class="staff">
  <div class="app">
    <?php include __DIR__ . '/../includes/staff_header.php'; ?>
    <?php include __DIR__ . '/../includes/staff_navbar.php'; ?>

    <main class="content">
      <div class="page-head d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h1 class="mb-0">Appointments</h1>
          <p class="mb-0">Create and reschedule bookings.</p>
        </div>
        <div>
          <a href="staff_booking/create_booking.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>&nbsp; New Appointment
          </a>
        </div>
      </div>

      <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Appointment created successfully!</div>
      <?php endif; ?>
      <?php if(isset($_GET['rescheduled'])): ?>
        <div class="alert alert-success">📅 Appointment rescheduled successfully!</div>
      <?php endif; ?>

      <div class="table-card">
        <table class="tablex">
          <thead>
            <tr>
              <th style="width:120px">Booking ID</th>
              <th style="width:15%">Franchisee</th>
              <th>Guest Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Schedule Date</th>
              <th style="width:10%">Status</th>
              <th style="width:15%">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          try {
              $sql = "SELECT 
                        b.booking_id, b.first_name, b.last_name, b.email, b.phone,
                        b.preferred_location, b.scheduled_date, b.status, b.notes,
                        f.business_name
                      FROM bookings b
                      LEFT JOIN franchisees f ON b.franchisee_id = f.franchisee_id
                      ORDER BY b.scheduled_date DESC";
              $stmt = $conn->query($sql);
              $rows = $stmt->fetchAll();

              if ($rows) {
                foreach ($rows as $row) {
                  $bid = (int)$row['booking_id'];
                  $statusClass = strtolower(trim($row['status'] ?? ''));
                  echo '<tr>';
                    echo '<td class="link toggle-details" data-target="d-' . $bid . '">
                            <i class="fas fa-chevron-right arrow-icon"></i> #BK-' . e((string)$bid) . '
                          </td>';
                    echo '<td>' . e((string)($row['business_name'] ?? 'N/A')) . '</td>';
                    echo '<td>' . e(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
                    echo '<td>' . e((string)$row['email']) . '</td>';
                    echo '<td>' . e((string)$row['phone']) . '</td>';
                    echo '<td>' . ($row['scheduled_date'] ? e(date("M d, Y h:i A", strtotime($row['scheduled_date']))) : '-') . '</td>';
                    echo '<td><span class="status-badge ' . e($statusClass) . '">' . e((string)$row['status']) . '</span></td>';
                    echo '<td>
                            <div class="action-buttons">
                              <a class="action-btn view-btn" href="staff_booking/reschedule_booking.php?id=' . $bid . '">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                              </a>
                            </div>
                          </td>';
                  echo '</tr>';

                  echo '<tr class="details-row" id="d-' . $bid . '">
                          <td colspan="8">
                            <div class="stack" style="gap:6px;">
                              <div><strong>Email:</strong> ' . e((string)$row['email']) . '</div>
                              <div><strong>Phone:</strong> ' . e((string)$row['phone']) . '</div>
                              <div><strong>Location:</strong> ' . e((string)$row['preferred_location']) . '</div>
                              <div><strong>Notes:</strong> ' . e((string)($row['notes'] ?? 'N/A')) . '</div>
                            </div>
                          </td>
                        </tr>';
                }
              } else {
                echo '<tr><td colspan="8" class="meta" style="text-align:center;"><em>No appointments found.</em></td></tr>';
              }
          } catch (Throwable $e) {
              echo '<tr><td colspan="8">Error: ' . e($e->getMessage()) . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
