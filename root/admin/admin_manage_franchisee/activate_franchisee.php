<?php
require_once "../../../db/config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $sql = "UPDATE franchisees SET status = 'Active' WHERE franchisee_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Activate error: " . $e->getMessage());
    }
}
header("Location: ../admin_manage_franchisee.php");
exit();
