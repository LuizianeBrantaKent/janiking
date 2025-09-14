<?php
// edit_user.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}
$id = intval($_GET['id']);

$name = $email = $phone = $role = $status = $password = "";
$errors = [];

// Load existing data
try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $role = $_POST["role"];
    $status = $_POST["status"];

    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (empty($role)) $errors[] = "Role is required.";
    if (empty($status)) $errors[] = "Status is required.";

    if (empty($errors)) {
        try {
            if (!empty($_POST["password"])) {
                $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name=?, email=?, phone=?, role=?, status=?, password_hash=?, updated_at=NOW() WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $phone, $role, $status, $password, $id]);
            } else {
                $sql = "UPDATE users SET name=?, email=?, phone=?, role=?, status=?, updated_at=NOW() WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $phone, $role, $status, $id]);
            }
            header("Location: ../admin_manage_users.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Update failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User - Janiking</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2>Edit User</h2>
  <?php if (!empty($errors)) { echo "<div class='alert alert-danger'>".implode("<br>", $errors)."</div>"; } ?>
  <form method="post">
    <div class="form-group">
      <label>Name</label>
      <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
    </div>
    <div class="form-group">
      <label>Password (leave blank to keep current)</label>
      <input type="password" name="password" class="form-control">
    </div>
    <div class="form-group">
      <label>Role</label>
      <select name="role" class="form-control">
        <option value="Admin" <?php if($user['role']=="Admin") echo "selected"; ?>>Admin</option>
        <option value="Manager" <?php if($user['role']=="Manager") echo "selected"; ?>>Manager</option>
        <option value="Staff" <?php if($user['role']=="Staff") echo "selected"; ?>>Staff</option>
      </select>
    </div>
    <div class="form-group">
      <label>Status</label>
      <select name="status" class="form-control">
        <option value="Active" <?php if($user['status']=="Active") echo "selected"; ?>>Active</option>
        <option value="Inactive" <?php if($user['status']=="Inactive") echo "selected"; ?>>Inactive</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="../admin_manage_users.php" class="btn btn-secondary">Cancel</a>
  </form>
</body>
</html>