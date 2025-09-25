<?php
// admin_manage_users.php

// Include database connection
require_once "../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$sql = "SELECT user_id, role, name, email, phone, status, created_at FROM users";

try {
    if (!empty($search)) {
        $sql .= " WHERE name LIKE :search OR email LIKE :search OR role LIKE :search";
    }
    $sql .= " ORDER BY user_id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
    }
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Initialize stats variables
$total_users = $active_users = $inactive_users = 0;
$roles = [];

// Stats query
try {
    $stats_query = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
    $stmt = $conn->query($stats_query);
    $stats_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats_result as $row) {
        $total_users += $row['count'];
        if ($row['status'] == 'Active') $active_users = $row['count'];
        elseif ($row['status'] == 'Inactive') $inactive_users = $row['count'];
    }
} catch (PDOException $e) {
    // Handle error silently or log it
    error_log("Stats query failed: " . $e->getMessage());
}

// Roles query
try {
    $roles_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $stmt = $conn->query($roles_query);
    $roles_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($roles_result as $row) {
        $roles[$row['role']] = $row['count'];
    }
} catch (PDOException $e) {
    // Handle error silently or log it
    error_log("Roles query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Manage Users</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>
<?php include('../includes/header.php'); include('../includes/admin_navbar.php'); ?>

<div class="main-container">
  <main class="main-content">
    <div class="top-controls">
      <h1 class="page-title">Manage Users</h1>
      <button class="btn btn-primary" onclick="window.location.href='admin_manage_user/manage_user_add.php'">
        <i class="fas fa-user-plus"></i> Add User
      </button>
    </div>

    <!-- Search -->
    <form class="search-box" method="get">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
    </form>

    <!-- Users Table -->
    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if (count($users) > 0) {
          foreach ($users as $row) {
            echo "<tr>";
            echo "<td>#USR-" . htmlspecialchars($row['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td><span class='status-badge " . strtolower($row['status']) . "'>" . htmlspecialchars($row['status']) . "</span></td>";
            echo "<td>" . htmlspecialchars(date("M d, Y", strtotime($row['created_at']))) . "</td>";
            echo "<td>
                    <div class='action-buttons'>
                      <a href='admin_manage_user/view_user.php?id=" . $row['user_id'] . "' class='action-btn view-btn'><i class='fas fa-eye'></i> View</a>
                      <a href='admin_manage_user/edit_user.php?id=" . $row['user_id'] . "' class='action-btn edit-btn'><i class='fas fa-edit'></i> Edit</a>";
            if ($row['status'] == "Active") {
              echo "<a href='admin_manage_user/deactivate_user.php?id=" . $row['user_id'] . "' class='action-btn deactivate-btn'><i class='fas fa-user-slash'></i> Deactivate</a>";
            } else {
              echo "<a href='admin_manage_user/activate_user.php?id=" . $row['user_id'] . "' class='action-btn activate-btn'><i class='fas fa-user-check'></i> Activate</a>";
            }
            echo "<a href='admin_manage_user/delete_user.php?id=" . $row['user_id'] . "' onclick=\"return confirm('Are you sure?');\" class='action-btn delete-btn'><i class='fas fa-trash'></i> Delete</a>
                    </div>
                  </td>";
            echo "</tr>";
          }
        } else {
          echo '<tr><td colspan="7" style="text-align:center;"><em>No users found.</em></td></tr>';
        }
        ?>
        </tbody>
      </table>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
      <div class="stat-card">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
      </div>
      <div class="stat-card">
        <h3>Active Users</h3>
        <p><?php echo $active_users; ?></p>
      </div>
      <div class="stat-card">
        <h3>Inactive Users</h3>
        <p><?php echo $inactive_users; ?></p>
      </div>
    </div>

    <!-- Roles Distribution -->
    <div class="card" style="padding:20px;">
      <h2 class="section-title">User Roles Distribution</h2>
      <?php
      if ($total_users > 0) {
        foreach ($roles as $role => $count) {
          $percentage = round(($count / $total_users) * 100);
          echo "<div class='role-item'>
                  <div class='role-name'>
                    <span>" . htmlspecialchars($role) . "</span>
                    <span>{$percentage}% ({$count})</span>
                  </div>
                  <div class='progress-bar'>
                    <div class='progress-fill' style='width: {$percentage}%;'></div>
                  </div>
                </div>";
        }
      } else {
        echo "<p>No users found.</p>";
      }
      ?>
    </div>
  </main>
</div>

<!-- External JS -->
<script src="../assets/js/script_admin.js"></script>
</body>
</html>