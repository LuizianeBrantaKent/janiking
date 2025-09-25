<?php
// admin_manage_appointments.php
require_once "../../db/config.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Manage Appointments</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>
  <?php include('../includes/header.php'); ?>
  <?php include('../includes/admin_navbar.php'); ?>

  <div class="main-container">
    <main class="main-content">
      <div class="top-controls">
        <h1 class="page-title">Manage Appointments</h1>
        <a href="admin_appointment/create_booking.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> New Appointment
        </a>
      </div>

      <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Appointment created successfully!</div>
      <?php endif; ?>
      <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success">🗑️ Booking deleted successfully!</div>
      <?php endif; ?>
      <?php if(isset($_GET['updated'])): ?>
        <div class="alert alert-success">✏️ Booking updated successfully!</div>
      <?php endif; ?>
      <?php if(isset($_GET['rescheduled'])): ?>
        <div class="alert alert-success">📅 Booking rescheduled successfully!</div>
      <?php endif; ?>

      <div class="table-card">
        <table>
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Franchisee</th>
              <th>Guest Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Schedule Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          try {
              $sql = "SELECT 
                        b.booking_id, b.first_name, b.last_name, b.email, b.phone, b.preferred_location,
                        b.scheduled_date, b.status, b.notes,
                        f.business_name
                      FROM bookings b 
                      LEFT JOIN franchisees f ON b.franchisee_id = f.franchisee_id 
                      ORDER BY b.scheduled_date DESC";
              $stmt = $conn->query($sql);
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($result) > 0) {
                  foreach ($result as $row) {
                      $statusClass = strtolower(trim($row['status']));
                      $bid = (int)$row['booking_id'];

                      echo "<tr>";
                      echo "<td class='link toggle-details' data-target='details-{$bid}'>
                              <i class='fas fa-chevron-right arrow-icon'></i> #BK-".htmlspecialchars($bid)."
                            </td>";
                      echo "<td>".htmlspecialchars($row['business_name'] ?? 'N/A')."</td>";
                      echo "<td>".htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''))."</td>";
                      echo "<td>".htmlspecialchars($row['email'])."</td>";
                      echo "<td>".htmlspecialchars($row['phone'])."</td>";
                      echo "<td>".($row['scheduled_date'] ? date("M d, Y h:i A", strtotime($row['scheduled_date'])) : '-')."</td>";
                      echo "<td><span class='status-badge {$statusClass}'>".htmlspecialchars($row['status'])."</span></td>";
                      echo "<td>
                              <div class='action-buttons'>
                                <a class='action-btn edit-btn' href='admin_appointment/update_booking.php?id={$bid}'>
                                  <i class='fas fa-exchange-alt'></i> Change
                                </a>
                                <a class='action-btn view-btn' href='admin_appointment/reschedule_booking.php?id={$bid}'>
                                  <i class='fas fa-calendar-alt'></i> Reschedule
                                </a>
                                <a class='action-btn delete-btn' href='admin_appointment/delete_booking.php?id={$bid}' onclick=\"return confirm('Are you sure?');\">
                                  <i class='fas fa-trash'></i> Delete
                                </a>
                              </div>
                            </td>";
                      echo "</tr>";

                      // Collapsible details row
                      echo "<tr class='details-row' id='details-{$bid}'>";
                      echo "<td colspan='8'>
                              <p><strong>Email:</strong> ".htmlspecialchars($row['email'])."</p>
                              <p><strong>Phone:</strong> ".htmlspecialchars($row['phone'])."</p>
                              <p><strong>Location:</strong> ".htmlspecialchars($row['preferred_location'])."</p>
                              <p><strong>Notes:</strong> ".htmlspecialchars($row['notes'] ?? 'N/A')."</p>
                            </td>";
                      echo "</tr>";
                  }
              } else {
                  echo '<tr><td colspan="8" style="text-align:center;"><em>No appointments found.</em></td></tr>';
              }
          } catch (PDOException $e) {
              echo '<tr><td colspan="8">Error fetching data: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.admin.js"></script>
</body>
</html>
