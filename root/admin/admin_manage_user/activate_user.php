<?php
// activate_user.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $sql = "UPDATE users SET status='Active', updated_at=NOW() WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Activation failed: " . $e->getMessage());
    }
}
header("Location: ../admin_manage_users.php");
exit();