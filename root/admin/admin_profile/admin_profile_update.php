<?php
// admin_profile_update.php
require_once "../../../db/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$admin_id = 1; // Replace with $_SESSION['user_id']

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role  = trim($_POST['role']);

    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE user_id = ?");
        $stmt->execute([$name, $email, $phone, $role, $admin_id]);

        header("Location: ../admin_profile.php?success=1");
        exit;
    } catch (PDOException $e) {
        die("Error updating profile: " . $e->getMessage());
    }
}

header("Location: ../admin_profile.php?error=1");
exit;
