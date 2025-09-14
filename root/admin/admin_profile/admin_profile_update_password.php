<?php
// admin_profile_update_password.php
require_once "../../../db/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$admin_id = 1; // Replace with $_SESSION['user_id']

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../admin_profile.php?error=Please fill in all password fields.");
        exit;
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../admin_profile.php?error=New passwords do not match.");
        exit;
    }

    try {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            header("Location: ../admin_profile.php?error=Current password is incorrect.");
            exit;
        }

        // Hash new password
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);

        // Update DB
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->execute([$hashed, $admin_id]);

        header("Location: ../admin_profile.php?success=Password updated successfully.");
        exit;

    } catch (PDOException $e) {
        header("Location: ../admin_profile.php?error=Error updating password: " . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: ../admin_profile.php?error=Invalid request.");
exit;
