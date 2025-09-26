<?php
date_default_timezone_set('Australia/Sydney'); // Set Sydney timezone

// Regenerate session ID and set secure parameters before session_start
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true, // Use HTTPS in production
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db/config.php';

    $email = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $role = htmlspecialchars(trim($_POST['role']));
    $errors = [];

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    }
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($role)) $errors[] = "Role is required.";
    if (!in_array($role, ['Admin', 'Staff', 'Franchisee'])) $errors[] = "Invalid role.";

    if (empty($errors)) {
        try {
            if ($role === 'Franchisee') {
                $stmt = $conn->prepare("SELECT franchisee_id, email, password_hash, point_of_contact AS name FROM franchisees WHERE email = ? AND status = 'Active'");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conn->prepare("SELECT user_id, email, password_hash, name, role FROM users WHERE email = ? AND role = ? AND status = 'Active'");
                $stmt->execute([$email, $role]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                if (($role === 'Franchisee' && $user) || ($role === $user['role'])) {
                    $_SESSION['user_id'] = $role === 'Franchisee' ? $user['franchisee_id'] : $user['user_id'];
                    $_SESSION['role'] = $role;
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['name'] ?? $user['point_of_contact'];
                    switch ($role) {
                        case 'Admin': header('Location: /admin/admin_dash.php'); break;
                        case 'Staff': header('Location: /staff/staff_dash.php'); break;
                        case 'Franchisee': header('Location: /franchisee/franchisee_dash.php'); break;
                    }
                    exit;
                } else {
                    $errors[] = "Role mismatch.";
                }
            } else {
                $errors[] = "Email/Username doesn't exist or password is incorrect for $role.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}
?>
<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Login Form HTML -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-stretch">
            <!-- Left Column -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="hero-left">
                    <h2 class="font-weight-bold mb-4">Welcome back to JaniKing Franchise Portal</h2>
                </div>
            </div>

            <!-- Right Column: Login Form -->
            <div class="col-md-6">
                <div class="login-form">
                    <h3 class="font-weight-bold mb-4">Login</h3>
                    <p class="lead mb-4">Please sign in to your account.</p>

                    <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                        <div class="alert alert-success">You have been logged out successfully.</div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label for="username">Email</label>
                            <input type="email" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Admin">JaniKing Admin</option>
                                <option value="Staff">Staff</option>
                                <option value="Franchisee">Franchisee</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
                    </form>

                    <p class="text-center mt-3"><a href="/reset_password.php">Forgot password?</a></p>
                    <p class="text-center mt-3">Need Help? <a href="/contact_us.php">Contact Us</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/guest_footer.php'; ?>