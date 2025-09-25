<?php
// edit_user.php
require_once "../../../db/config.php";
session_start();

// Check DB connection
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// Validate user ID
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

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $role = $_POST["role"];
    $status = $_POST["status"];

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($role)) $errors[] = "Role is required.";
    if (empty($status)) $errors[] = "Status is required.";

    // Phone (optional, AU format if provided)
    if (!empty($phone)) {
        $phone_input = preg_replace('/[^0-9+]/', '', $phone);
        if (!preg_match('/^(\+61|0)[0-9]{9}$/', $phone_input)) {
            $errors[] = "Invalid Australian phone number format.";
        } else {
            $phone = $phone_input;
        }
    }

    // Password validation if provided
    if (!empty($_POST["password"])) {
        if (
            !preg_match(
                "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/",
                $_POST["password"]
            )
        ) {
            $errors[] = "Password must be at least 8 characters, include uppercase, lowercase, a number, and a special character.";
        } else {
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        }
    }

    // If no errors, update DB
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $sql = "UPDATE users 
                        SET name=?, email=?, phone=?, role=?, status=?, password_hash=?, updated_at=NOW() 
                        WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $phone, $role, $status, $password, $id]);
            } else {
                $sql = "UPDATE users 
                        SET name=?, email=?, phone=?, role=?, status=?, updated_at=NOW() 
                        WHERE user_id=?";
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
  <!-- External styles -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Always from root -->
  <link rel="stylesheet" href="/assets/css/style_admin.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
    <main class="main-content">
    <h1 class="page-title">Edit User</h1>

    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <?= implode("<br>", $errors) ?>
        </div>
    <?php } ?>

    <form method="post">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" placeholder="e.g. John Smith" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="e.g. name@example.com" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" placeholder="+61 400 000 000" pattern="^(\+61|0)[0-9]{9}$" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
      </div>
      <div class="form-group">
        <label>Password (leave blank to keep current)</label>
        <input type="password" name="password" placeholder="Min. 8 chars, include uppercase, lowercase, number & symbol" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$" class="form-control">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control" required>
          <option value="Admin" <?= $user['role']=="Admin" ? "selected" : "" ?>>Admin</option>
          <option value="Manager" <?= $user['role']=="Manager" ? "selected" : "" ?>>Manager</option>
          <option value="Staff" <?= $user['role']=="Staff" ? "selected" : "" ?>>Staff</option>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control" required>
          <option value="Active" <?= $user['status']=="Active" ? "selected" : "" ?>>Active</option>
          <option value="Inactive" <?= $user['status']=="Inactive" ? "selected" : "" ?>>Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="../admin_manage_users.php" class="btn btn-secondary">Cancel</a>
    </form>
    </main>
    </div>

    <!-- External JS -->
    <script src="/assets/js/script_admin.js"></script>
</body>
</html>
