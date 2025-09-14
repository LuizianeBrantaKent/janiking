<?php
// manage_user_add.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

$name = $email = $role = $status = $phone = $password = "";
$name_err = $email_err = $role_err = $status_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen($_POST["password"]) < 6) {
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT); // Hash password
    }

    // Validate role
    if (empty($_POST["role"])) {
        $role_err = "Please select a role.";
    } else {
        $role = $_POST["role"];
    }

    // Validate status
    if (empty($_POST["status"])) {
        $status_err = "Please select a status.";
    } else {
        $status = $_POST["status"];
    }

    // Phone (optional)
    $phone = trim($_POST["phone"]);

    // If no errors, insert into DB
    if (empty($name_err) && empty($email_err) && empty($role_err) && empty($status_err) && empty($password_err)) {
        try {
            $sql = "INSERT INTO users (name, email, phone, role, status, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $phone, $role, $status, $password]);
            header("location: ../admin_manage_users.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error: Could not save user. " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User - Janiking</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2>Add New User</h2>
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Name</label>
      <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
      <span class="invalid-feedback"><?php echo $name_err; ?></span>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
      <span class="invalid-feedback"><?php echo $email_err; ?></span>
    </div>
    <div class="form-group">
      <label>Phone (optional)</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
      <span class="invalid-feedback"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
      <label>Role</label>
      <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
        <option value="">-- Select Role --</option>
        <option value="Admin" <?php if($role=="Admin") echo "selected"; ?>>Admin</option>
        <option value="Manager" <?php if($role=="Manager") echo "selected"; ?>>Manager</option>
        <option value="Staff" <?php if($role=="Staff") echo "selected"; ?>>Staff</option>
      </select>
      <span class="invalid-feedback"><?php echo $role_err; ?></span>
    </div>
    <div class="form-group">
      <label>Status</label>
      <select name="status" class="form-control <?php echo (!empty($status_err)) ? 'is-invalid' : ''; ?>">
        <option value="">-- Select Status --</option>
        <option value="Active" <?php if($status=="Active") echo "selected"; ?>>Active</option>
        <option value="Inactive" <?php if($status=="Inactive") echo "selected"; ?>>Inactive</option>
      </select>
      <span class="invalid-feedback"><?php echo $status_err; ?></span>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="../admin_manage_users.php" class="btn btn-secondary">Cancel</a>
  </form>
</body>
</html>