<?php
// admin_dash.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --header-h: 80px;   /* match header.php fixed height */
      --nav-w: 220px;     /* match admin_navbar.php width */
      --brand-blue: #004990;
    }

    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    body{
      background:#f8f9fa;
      color:#333;
      height:100vh;
      overflow:hidden; /* scroll inside main-content only */
    }

    /* --- Layout that coordinates header + navbar + content --- */
    /* If your included files already fix header/navbar, these rules ensure spacing. */
    .main-container {
    display: flex;
    height: 100vh;
    padding-top: 80px; /* header height */
    }

    /* Ensure any included sidebar sits under header and spans the rest of the viewport */
    .admin-navbar{
      position:fixed !important;
      top:var(--header-h) !important;
      left:0 !important;
      width:var(--nav-w) !important;
      height:calc(100vh - var(--header-h)) !important;
      overflow-y:auto;
      background:#fff;
      border-right:1px solid #ddd;
      z-index: 900; /* below header if header has higher z-index */
    }

    /* Content area to the right of the sidebar */
    .main-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
    background: #e9ecef; /* same as body to make it seamless with header */
    }

    /* Quick Actions (blue cards) */
    .quick-actions{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
      gap:15px;
      margin-bottom:30px;
    }
    .action-card{
      background:var(--brand-blue);
      padding:20px;
      border-radius:10px;
      text-align:center;
      box-shadow:0 4px 10px rgba(0,0,0,0.08);
      transition:transform .3s ease;
      cursor:pointer;
    }
    .action-card:hover{ transform:translateY(-5px); }
    .action-card i,
    .action-card h3,
    .action-card p{ color:#fff; }
    .action-card i{ font-size:24px; margin-bottom:12px; }
    .action-card h3{ font-size:16px; margin-bottom:8px; }
    .action-card p{ font-size:13px; }

    /* Sections */
    .welcome-section h1{ font-size:28px; margin-bottom:10px; }
    .welcome-section p{ opacity:.9; margin-bottom:20px; }

    .analytics-section{ margin-bottom:30px; }
    .section-title{
      color: var(--brand-blue);
      font-size:22px;
      margin-bottom:20px;
      padding-bottom:10px;
      border-bottom:1px solid #ffffffff;
    }
    .analytics-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
      gap:20px;
      margin-bottom:25px;
    }
    .analytics-card{
      background:#fff;
      padding:20px;
      border-radius:10px;
      box-shadow:0 4px 10px rgba(0,0,0,0.08);
    }
    .analytics-card h3{ font-size:16px; margin-bottom:15px; color:var(--brand-blue); }
    .stat{ display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
    .stat-value{ font-size:24px; font-weight:700; color:var(--brand-blue); }
    .stat-change{ background:#e8f5e9; color:#2e7d32; padding:4px 8px; border-radius:20px; font-size:12px; font-weight:600; }
    .stat-change.negative{ background:#ffebee; color:#c62828; }
    .chart-placeholder{
      background:#f8f9fa; height:150px; border-radius:8px;
      display:flex; align-items:center; justify-content:center; color:#6c757d; margin-top:15px;
    }

    .notifications-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));
      gap:20px;
    }
    .notification-card{
      background:#fff; padding:20px; border-radius:10px;
      box-shadow:0 4px 10px rgba(0,0,0,0.08);
    }
    .notification-card h3{
      font-size:16px; margin-bottom:15px; color:var(--brand-blue);
      display:flex; align-items:center; gap:10px;
    }
    .notification-card h3 i{ color:#dc3545; }
    .notification-item{ padding:12px 0; border-bottom:1px solid #f1f1f1; }
    .notification-item:last-child{ border-bottom:none; }
    .notification-item p{ margin-bottom:8px; font-size:14px; }
    .notification-action{ color:var(--brand-blue); font-weight:600; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
    .notification-action:hover{ text-decoration:underline; }

    /* Responsive: collapse sidebar width var */
    @media (max-width: 992px){
      :root{ --nav-w: 80px; }
      .main-content{ padding:20px 15px; }
      .section-title{ font-size:20px; }
    }
    @media (max-width: 576px){
      /* If your navbar switches to top layout on phones, you can override here. */
      .main-content{ margin-left:0; }
    }
  </style>
</head>
<body>

  <?php
    // Fixed header (should include the logo from /assets/logo.png inside header.php)
    include('../includes/header.php');

    // Fixed sidebar below header
    include('../includes/admin_navbar.php');
  ?>

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
          <i class="fas fa-calendar-plus"></i>
          <h3>New Appointment</h3>
          <p>Schedule a new appointment</p>
        </div>
        <div class="action-card">
          <i class="fas fa-cube"></i>
          <h3>Add Inventory</h3>
          <p>Add new products to inventory</p>
        </div>
        <div class="action-card">
          <i class="fas fa-user-plus"></i>
          <h3>Add Users</h3>
          <p>Create new user accounts</p>
        </div>
        <div class="action-card">
          <i class="fas fa-bullhorn"></i>
          <h3>Send Announcements</h3>
          <p>Send messages to users</p>
        </div>
        <div class="action-card">
          <i class="fas fa-chart-pie"></i>
          <h3>Generate Reports</h3>
          <p>Create performance reports</p>
        </div>
      </div>

      <!-- Analytics -->
      <section class="analytics-section">
        <h2 class="section-title">Analytics Overview</h2>

        <div class="analytics-grid">
          <div class="analytics-card">
            <h3>Bookings</h3>
            <div class="stat">
              <span class="stat-value">327</span>
              <span class="stat-change">+12%</span>
            </div>
            <p>From last month</p>
            <div class="chart-placeholder">Booking Statistics Chart</div>
            <a href="#" class="notification-action">View Details</a>
          </div>

          <div class="analytics-card">
            <h3>Products</h3>
            <div class="stat">
              <span class="stat-value">1,358</span>
              <span class="stat-change">+5%</span>
            </div>
            <p>From last month</p>
            <div class="chart-placeholder">Product Sales Chart</div>
            <a href="#" class="notification-action">View Details</a>
          </div>

          <div class="analytics-card">
            <h3>Users</h3>
            <div class="stat">
              <span class="stat-value">842</span>
              <span class="stat-change">+18%</span>
            </div>
            <p>From last month</p>
            <div class="chart-placeholder">User Growth Chart</div>
            <a href="#" class="notification-action">View Details</a>
          </div>
        </div>
      </section>

      <!-- Notifications -->
      <section class="notifications-section">
        <h2 class="section-title">Notifications</h2>

        <div class="notifications-grid">
          <div class="notification-card">
            <h3><i class="fas fa-exclamation-circle"></i> Appointments Awaiting Confirmation</h3>
            <div class="notification-item">
              <p>New appointments need your approval</p>
              <a href="#" class="notification-action">Review Now <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>

          <div class="notification-card">
            <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
            <div class="notification-item">
              <p>Items requiring immediate restock</p>
              <a href="#" class="notification-action">View Inventory <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>

          <div class="notification-card">
            <h3><i class="fas fa-user-clock"></i> New User Registrations</h3>
            <div class="notification-item">
              <p>New users awaiting approval</p>
              <a href="#" class="notification-action">Approve Users <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>

          <div class="notification-card">
            <h3><i class="fas fa-file-upload"></i> Document Upload Required</h3>
            <div class="notification-item">
              <p>Training materials need updating</p>
              <a href="#" class="notification-action">Upload Files <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Highlight the current page link in the sidebar if present
    document.addEventListener('DOMContentLoaded', function(){
      const navLinks = document.querySelectorAll('.admin-navbar a.nav-link, .admin-navbar a');
      const here = location.pathname.replace(/\/+$/, '');
      navLinks.forEach(a=>{
        const href = a.getAttribute('href') || '';
        if (!href) return;
        const path = href.replace(/\/+$/, '');
        if (path.endsWith('admin_dash.php')) {
          a.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>
