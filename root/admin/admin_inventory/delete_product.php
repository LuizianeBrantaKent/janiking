<?php
// delete_product.php
require_once "../../../db/config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval($_GET["id"]);

    try {
        $sql = "DELETE FROM products WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            header("location: ../admin_manage_inventory.php?deleted=1");
            exit;
        } else {
            echo "Error deleting product.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("location: ../admin_manage_inventory.php");
    exit;
}