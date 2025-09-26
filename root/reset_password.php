<?php
date_default_timezone_set('Australia/Sydney'); // Set Sydney timezone
session_start();
include '../db/config.php';
?>
<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Reset Password Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-stretch">
            <!-- Left Column: Hero with Image and Text -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="hero-left">
                    <div>
                        <h2 class="font-weight-bold mb-4">Reset Your Password</h2>
                        <p class="lead mb-4">Enter your email to receive a password reset link.</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Reset Password Form -->
            <div class="col-md-6">
                <div class="login-form">
                    <h3 class="font-weight-bold mb-4">Reset Password</h3>
                    <?php
                    $hcaptcha_secret = '0x0000000000000000000000000000000000000000'; // Replace with your actual secret key
                    $errors = [];
                    $success = '';

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $email = htmlspecialchars(trim($_POST['email']));
                        
                        // hCaptcha verification
                        if (isset($_POST['h-captcha-response'])) {
                            $hcaptcha_response = $_POST['h-captcha-response'];
                            $data = ['secret' => $hcaptcha_secret, 'response' => $hcaptcha_response];
                            $verify = curl_init();
                            curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
                            curl_setopt($verify, CURLOPT_POST, true);
                            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
                            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($verify);
                            $responseData = json_decode($response);
                            if (!$responseData->success) {
                                $errors[] = "CAPTCHA verification failed. Please try again.";
                            }
                        } else {
                            $errors[] = "Please complete the CAPTCHA.";
                        }

                        if (empty($email)) {
                            $errors[] = "Email is required.";
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Invalid email format.";
                        }

                        if (empty($errors)) {
                            try {
                                // Check users table (Admin/Staff)
                                $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE email = ? AND status = 'Active'");
                                $stmt->execute([$email]);
                                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                // Check franchisees table (Franchisee)
                                if (!$user) {
                                    $stmt = $conn->prepare("SELECT franchisee_id, email FROM franchisees WHERE email = ? AND status = 'Active'");
                                    $stmt->execute([$email]);
                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                }

                                if ($user) {
                                    $token = bin2hex(random_bytes(32));
                                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                                    // Store token in password_resets table
                                    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                                    $stmt->execute([$email, $token, $expires_at]);

                                    // Simulate email sending (replace with PHPMailer in production)
                                    $reset_link = "http://localhost/reset_password_confirm.php?token=" . $token;
                                    $success = "A password reset link has been sent to " . htmlspecialchars($email) . ". 
                                                Please check your inbox. (Simulated for testing: <a href='" . $reset_link . "'>Click here</a>)";
                                } else {
                                    $errors[] = "No account found with that email.";
                                }
                            } catch (PDOException $e) {
                                $errors[] = "Database error: " . $e->getMessage();
                            }
                        }
                    }

                    // Display messages
                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        foreach ($errors as $error) {
                            echo '<p>' . $error . '</p>';
                        }
                        echo '</div>';
                    }
                    if ($success) {
                        echo '<div class="alert alert-success">' . $success . '</div>';
                    }

                    // Reset form (initial step)
                    if (!isset($_GET['token']) || (isset($_GET['step']) && $_GET['step'] !== 'reset')) {
                        ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <!-- hCaptcha -->
                                <div class="h-captcha" data-sitekey="10000000-ffff-ffff-ffff-000000000001"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Send Reset Link</button>
                        </form>
                        <p class="text-center mt-3">
                            <a href="/login.php">Back to Login</a>
                        </p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/guest_footer.php'; ?>
