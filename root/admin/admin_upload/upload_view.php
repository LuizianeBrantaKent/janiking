<?php
// upload_view.php
require_once "../../../db/config.php"; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid file ID.");
}

$id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT * FROM training WHERE training_id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        die("File not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View File</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-preview iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
        <main class="main-content">
            <h1 class="page-title">File Details</h1>

            <div class="card shadow-sm p-4 mb-4">
                <p><strong>Title:</strong> <?php echo htmlspecialchars($file['title']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($file['description']); ?></p>
                <p><strong>Upload Date:</strong> <?php echo date("M d, Y", strtotime($file['created_at'])); ?></p>

                <div class="mb-3">
                    <a href="../../assets/uploads/<?php echo htmlspecialchars($file['file_path']); ?>" 
                       class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a href="../admin_uploads.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <!-- File Preview -->
                <div class="file-preview mt-4">
                    <h5><i class="fas fa-eye"></i> Preview</h5>
                    <iframe src="../../assets/uploads/<?php echo htmlspecialchars($file['file_path']); ?>" allowfullscreen></iframe>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
