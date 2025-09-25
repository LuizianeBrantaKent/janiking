<?php
// view_user.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$user = null;

try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View User - Janiking</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
      <main class="main-content">
        <h1 class="page-title">User Details</h1>

        <ul class="list-group">
          <li class="list-group-item"><strong>User ID:</strong> <?php echo "#USR-" . htmlspecialchars($user['user_id']); ?></li>
          <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></li>
          <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
          <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></li>
          <li class="list-group-item"><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></li>
          <li class="list-group-item"><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></li>
          <li class="list-group-item"><strong>Created:</strong> <?php echo date("M d, Y h:i A", strtotime($user['created_at'])); ?></li>
          <li class="list-group-item"><strong>Last Updated:</strong> 
            <?php echo isset($user['updated_at']) ? date("M d, Y h:i A", strtotime($user['updated_at'])) : 'Never'; ?>
          </li>
        </ul>

        <a href="../admin_manage_users.php" class="btn btn-secondary mt-3">Back</a>
      </main>
    </div>
</body>
</html>
