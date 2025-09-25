<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

require_once "../../../db/config.php";

if (!isset($_GET['id'])) {
    header("Location: ../admin_documents.php");
    exit;
}

$id = intval($_GET['id']);

try {
    // Fetch file to delete
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE documents_id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Delete the physical file
        $filePath = $file['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $stmt = $conn->prepare("DELETE FROM documents WHERE documents_id = ?");
        $stmt->execute([$id]);
        $success = "Document deleted successfully.";
    } else {
        $error = "Document not found.";
    }
} catch (PDOException $e) {
    $error = "Error deleting document: " . $e->getMessage();
}

// Store messages in session to display after redirect
session_start();
if (isset($error)) {
    $_SESSION['error'] = $error;
} elseif (isset($success)) {
    $_SESSION['success'] = $success;
}

header("Location: ../admin_documents.php");
exit;

ob_end_flush();
?>