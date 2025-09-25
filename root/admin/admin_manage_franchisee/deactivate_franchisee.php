<?php
require_once "../../../db/config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $sql = "UPDATE franchisees SET status = 'Inactive' WHERE franchisee_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Deactivate error: " . $e->getMessage());
    }
}
header("Location: ../admin_manage_franchisee.php");
exit();
