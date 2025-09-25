<?php
// training_delete.php
require_once "../../../db/config.php"; // uses $conn (PDO)

if (!isset($_GET['id'])) {
    header("Location: ../admin_trainings.php");
    exit;
}

$id = intval($_GET['id']);

try {
    // Fetch file to delete
    $stmt = $conn->prepare("SELECT file_path FROM training WHERE training_id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Delete the physical file
        $filePath = "../../assets/uploads/" . $file['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $stmt = $conn->prepare("DELETE FROM training WHERE training_id = ?");
        $stmt->execute([$id]);
    }
} catch (PDOException $e) {
    die("Error deleting file: " . $e->getMessage());
}

header("Location: ../admin_trainings.php");
exit;
?>
