<?php
// delete_user.php
require_once "../../../db/config.php";

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $sql = "DELETE FROM users WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Deletion failed: " . $e->getMessage());
    }
}
header("Location: ../admin_manage_users.php");
exit();