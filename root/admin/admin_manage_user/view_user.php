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
</head>
<body class="container mt-5">
  <h2>User Details</h2>

  <div class="form-group">
    <label>User ID</label>
    <input type="text" class="form-control" value="<?php echo "#USR-" . htmlspecialchars($user['user_id']); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Name</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Email</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Phone</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Role</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Status</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['status']); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Created</label>
    <input type="text" class="form-control" value="<?php echo date("M d, Y h:i A", strtotime($user['created_at'])); ?>" readonly>
  </div>
  <div class="form-group">
    <label>Last Updated</label>
    <input type="text" class="form-control" value="<?php echo isset($user['updated_at']) ? date("M d, Y h:i A", strtotime($user['updated_at'])) : 'Never'; ?>" readonly>
  </div>

  <a href="../admin_manage_users.php" class="btn btn-secondary">Back</a>
</body>
</html>