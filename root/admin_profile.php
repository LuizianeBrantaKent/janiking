<?php
require_once "../../db/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulated logged-in admin (replace with $_SESSION['user_id'])
$admin_id = 1;

// Fetch admin details
$stmt = $conn->prepare("SELECT user_id, name, email, phone, role FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found in database.");
}

include('../includes/header.php');
include('../includes/admin_navbar.php');

// Helper function for escaping output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Profile Settings</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>

  <div class="main-container">
    <main class="main-content">
      <h1 class="page-title">Profile Settings</h1>
      <p class="page-subtitle">View and update your personal information and preferences</p>

      <div class="two-column-layout">
        <!-- Left Column -->
        <div>
          <!-- Personal Information -->
          <div class="profile-section">
            <h2 class="section-title">Personal Information</h2>

            <form method="post" action="admin_profile/admin_profile_update.php">
              <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" name="name" id="name" 
                       value="<?= e($admin['name']); ?>" 
                       placeholder="Enter your full name" required>
                <small class="form-text text-muted">Use your official name as registered in the system.</small>
              </div>

              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" 
                       value="<?= e($admin['email']); ?>" 
                       placeholder="example@domain.com" required>
                <small class="form-text text-muted">Use a valid email for notifications and login.</small>
              </div>

              <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" name="phone" id="phone" 
                       value="<?= e($admin['phone']); ?>" 
                       placeholder="e.g., 0412345678">
                <small class="form-text text-muted">Include area code if required. Digits only.</small>
              </div>

              <div class="form-group">
                <label for="role">Position / Role</label>
                <input type="text" class="form-control" name="role" id="role" 
                       value="<?= e($admin['role']); ?>" 
                       placeholder="Admin, Staff, etc." required>
                <small class="form-text text-muted">Specify your role within the company.</small>
              </div>

              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
              </button>
            </form>
          </div>
        </div>

        <!-- Right Column -->
        <div class="profile-section">
          <h2 class="section-title">Change Password</h2>
          <form method="post" action="admin_profile/admin_profile_update_password.php">
            <div class="form-group">
              <label for="current-password">Current Password</label>
              <input type="password" class="form-control" name="current_password" id="current-password" placeholder="Enter current password" required>
            </div>

            <div class="form-group">
              <label for="new-password">New Password</label>
              <input type="password" class="form-control" name="new_password" id="new-password" placeholder="Enter new password" required>
              <small class="form-text text-muted">Minimum 8 characters recommended.</small>
            </div>

            <div class="form-group">
              <label for="confirm-password">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" id="confirm-password" placeholder="Re-enter new password" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <!-- External JS -->
  <script src="../assets/js/script_admin.js"></script>
</body>
</html>
