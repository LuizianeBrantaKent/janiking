<link rel="stylesheet" href="../assets/css/style_admin.css">
<div class="admin-navbar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="/admin/admin_dash.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_dash.php') echo 'active'; ?>">
                <i class="fas fa-th-large"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_manage_appointments.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_manage_appointments.php') echo 'active'; ?>">
                <i class="fas fa-calendar-check"></i>
                <span class="nav-text">Manage Appointments</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_manage_inventory.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_manage_inventory.php') echo 'active'; ?>">
                <i class="fas fa-boxes"></i>
                <span class="nav-text">Manage Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_manage_users.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_manage_users.php') echo 'active'; ?>">
                <i class="fas fa-users"></i>
                <span class="nav-text">Manage Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_manage_franchisee.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_manage_franchisee.php') echo 'active'; ?>">
                <i class="fas fa-store"></i>
                <span class="nav-text">Manage Franchisee</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_announcements.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_announcements.php') echo 'active'; ?>">
                <i class="fas fa-bullhorn"></i>
                <span class="nav-text">Communication</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_reports.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_reports.php') echo 'active'; ?>">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_uploads.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_uploads.php') echo 'active'; ?>">
                <i class="fas fa-file-upload"></i>
                <span class="nav-text">Upload Training & Documents</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/admin_profile.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin_profile.php') echo 'active'; ?>">
                <i class="fas fa-user-cog"></i>
                <span class="nav-text">Profile / Settings</span>
            </a>
        </li>
    </ul>

    <div class="logout-section">
        <a href="../logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
</div>

  <!-- External JS -->
  <script src="../assets/js/script_admin.js"></script>