<?php
date_default_timezone_set('Australia/Sydney'); // Set Sydney timezone
session_start();
include '../db/config.php';
?>
<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Reset Password Confirmation Section -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-form">
                    <h2 class="font-weight-bold mb-4 text-center">Reset Your Password</h2>
                    
                    <?php
                    $errors = [];
                    $success = '';
                    $valid_token = false;
                    $email = '';
                    
                    if (isset($_GET['token'])) {
                        $token = $_GET['token'];
                        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
                        $stmt->execute([$token]);
                        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($reset) {
                            $valid_token = true;
                            $email = $reset['email'];
                        } else {
                            $errors[] = "Invalid or expired reset token.";
                        }
                    } else {
                        $errors[] = "No reset token provided.";
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
                        $new_password = trim($_POST['password']);
                        $confirm_password = trim($_POST['confirm_password']);

                        if (empty($new_password)) $errors[] = "New password is required.";
                        elseif (strlen($new_password) < 6) $errors[] = "Password must be at least 6 characters long.";

                        if ($new_password !== $confirm_password) $errors[] = "Passwords do not match.";

                        if (empty($errors)) {
                            try {
                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                               // Check which table the email belongs to
                                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                                $stmt->execute([$email]);
                                if ($stmt->fetch()) {
                                    $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                                    $update_stmt->execute([$hashed_password, $email]);
                                } else {
                                    $update_stmt = $conn->prepare("UPDATE franchisees SET password_hash = ? WHERE email = ?");
                                    $update_stmt->execute([$hashed_password, $email]);
                                }
                                $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
                                $success = "Your password has been reset successfully.<br><a href='/login.php' class='btn btn-primary mt-3' style='display:inline-block; color:#fff;'>Login Now</a>";

                            } catch (PDOException $e) {
                                $errors[] = "Database error: " . $e->getMessage();
                            }
                        }
                    }

                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        foreach ($errors as $error) echo '<p>' . $error . '</p>';
                        echo '</div>';
                    }

                    if ($success) {
                        echo '<div class="alert alert-success text-center">' . $success . '</div>';
                    }

                    if ($valid_token && !$success) {
                    ?>
                    <div class="alert alert-info">
                        <strong>Reset password for:</strong> <?php echo htmlspecialchars($email); ?>
                    </div>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <small class="form-text text-muted">Must be at least 6 characters long.</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">Reset Password</button>
                    </form>

                    <p class="text-center mt-3">
                        <a href="/login.php">Back to Login</a>
                    </p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/guest_footer.php'; ?>
