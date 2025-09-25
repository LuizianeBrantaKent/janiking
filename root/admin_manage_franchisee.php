<?php
// admin_manage_franchisees.php

require_once "../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

try {
    // Base query
    $sql = "SELECT franchisee_id, business_name, address, abn, status, start_date, point_of_contact, phone, email FROM franchisees";

    if (!empty($search)) {
        $sql .= " WHERE point_of_contact LIKE :search 
                  OR business_name LIKE :search 
                  OR email LIKE :search";
    }

    $sql .= " ORDER BY franchisee_id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
    }
    
    $stmt->execute();
    $franchisees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Fetch statistics
$total_franchisees = 0;
$active_franchisees = 0;
$inactive_franchisees = 0;

try {
    $stats_query = "SELECT status, COUNT(*) as count FROM franchisees GROUP BY status";
    $stmt = $conn->query($stats_query);
    $stats_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stats_result as $row) {
        $total_franchisees += $row['count'];
        if ($row['status'] == 'Active') {
            $active_franchisees = $row['count'];
        } elseif ($row['status'] == 'Inactive') {
            $inactive_franchisees = $row['count'];
        }
    }
} catch (PDOException $e) {
    error_log("Stats query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Manage Franchisees</title>
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
      <h1 class="page-title">Manage Franchisees</h1>

      <!-- Controls -->
      <div class="top-controls">
        <form class="search-box" method="get">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search franchisees..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
        <button class="btn btn-primary" onclick="window.location.href='admin_manage_franchisee/franchisee_add.php'">
          <i class="fas fa-plus"></i> Add Franchisee
        </button>
      </div>

      <!-- Franchisees Table -->
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Franchisee ID</th>
              <th>Business Name</th>
              <th>Point of Contact</th>
              <th>Email</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php
if (count($franchisees) > 0) {
  foreach ($franchisees as $row) {
    $statusClass = strtolower($row['status']); // Add status class for styling
    echo "<tr>";
    echo "<td>#FR-" . htmlspecialchars($row['franchisee_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['business_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['point_of_contact']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>";
    echo "<td>
            <div class='action-buttons'>
              <a href='admin_manage_franchisee/view_franchisee.php?id=" . $row['franchisee_id'] . "' class='action-btn view-btn'><i class='fas fa-eye'></i> View</a>
              <a href='admin_manage_franchisee/edit_franchisee.php?id=" . $row['franchisee_id'] . "' class='action-btn edit-btn'><i class='fas fa-edit'></i> Edit</a>";
    if ($row['status'] == "Active") {
      echo "<a href='admin_manage_franchisee/deactivate_franchisee.php?id=" . $row['franchisee_id'] . "' class='action-btn deactivate-btn'><i class='fas fa-user-slash'></i> Deactivate</a>";
    } else {
      echo "<a href='admin_manage_franchisee/activate_franchisee.php?id=" . $row['franchisee_id'] . "' class='action-btn activate-btn'><i class='fas fa-user-check'></i> Activate</a>";
    }
    echo "<a href='admin_manage_franchisee/delete_franchisee.php?id=" . $row['franchisee_id'] . "' onclick=\"return confirm('Are you sure?');\" class='action-btn delete-btn'><i class='fas fa-trash'></i> Delete</a>
            </div>
          </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6' style='text-align:center;'>No franchisees found.</td></tr>";
}
?>
          </tbody>
        </table>
      </div>

      <!-- Statistics -->
      <div class="stats-container">
        <div class="stat-card">
          <h3>Total Franchisees</h3>
          <p><?php echo $total_franchisees; ?></p>
        </div>
        <div class="stat-card">
          <h3>Active Franchisees</h3>
          <p><?php echo $active_franchisees; ?></p>
        </div>
        <div class="stat-card">
          <h3>Inactive Franchisees</h3>
          <p><?php echo $inactive_franchisees; ?></p>
        </div>
      </div>
    </main>
  </div>

  <!-- External JS -->
  <script src="../assets/js/script_admin.js"></script>
</body>
</html>