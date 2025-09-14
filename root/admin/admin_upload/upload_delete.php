<?php
include('../../../db/config.php');

if(!isset($_GET['id'])){
    header("Location: ../admin_uploads.php");
    exit;
}

$id = $_GET['id'];

// Fetch file to delete from server
$stmt = $link->prepare("SELECT file_path FROM training WHERE training_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if($file){
    // Delete the physical file
    $filePath = "../assets/uploads/" . $file['file_path'];
    if(file_exists($filePath)){
        unlink($filePath);
    }

    // Delete database record
    $stmt = $link->prepare("DELETE FROM training WHERE training_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../admin_uploads.php");
exit;
?>
