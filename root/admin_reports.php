<?php
// admin_reports.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Reports</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>

<?php
include('../includes/header.php');
include('../includes/admin_navbar.php');
?>

<div class="main-container">
  <main class="main-content">
    <h1 class="page-title">Reports</h1>
    <p class="page-subtitle">Generate and export reports for your business</p>

    <!-- Generate Report Section -->
    <div class="generate-section">
      <h2 class="section-title">Generate New Report</h2>
      
      <form action="admin_reports/generate_report.php" method="POST">
        <div class="form-group">
          <label for="report-type">Report Type</label>
          <select class="form-control" id="report-type" name="report-type" required>
            <option value="">Select Report Type</option>
            <option value="inventory">Inventory Report</option>
            <option value="users">User Report</option>
            <option value="franchisees">Franchisee Report</option>
            <option value="bookings">Booking Report</option>
          </select>
        </div>

        <!-- Date range (only applies to users, franchisees, bookings) -->
        <div class="date-range">
          <div class="form-group">
            <label for="start-date">Start Date</label>
            <input type="date" class="form-control" id="start-date" name="start_date">
          </div>
          <div class="form-group">
            <label for="end-date">End Date</label>
            <input type="date" class="form-control" id="end-date" name="end_date">
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-file-excel"></i> Generate Excel Report
        </button>
      </form>
    </div>
  </main>
</div>

  <!-- External JS -->
  <script src="../assets/js/script_admin.js"></script>

</body>
</html>