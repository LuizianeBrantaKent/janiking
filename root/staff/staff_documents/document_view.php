<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

require_once "../../../db/config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid file ID.");
}

$id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("
        SELECT d.*, f.business_name 
        FROM documents d 
        LEFT JOIN franchisees f ON d.franchisee_id = f.franchisee_id 
        WHERE d.documents_id = ?
    ");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        die("File not found.");
    }

    // Clean file_path if it contains directory prefix
    $filePath = $file['file_path'];
    if (strpos($filePath, '../assets/uploads/') === 0) {
        $filePath = basename($filePath);
    }

    // Construct file paths
    $serverPath = "../../assets/uploads/" . $filePath; // Server-side path for file check
    $webPath = "/assets/uploads/" . $filePath; // Web-accessible path for URLs
    if (!file_exists($serverPath)) {
        $error = "File not found on server: " . htmlspecialchars($filePath);
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-preview iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include('../../includes/staff_header.php'); ?>
    <?php include('../../includes/staff_navbar.php'); ?>

    <div class="main-container">
        <main class="main-content">
            <h1 class="page-title">Document Details</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm p-4 mb-4">
                <p><strong>Title:</strong> <?php echo htmlspecialchars($file['title']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($file['description']); ?></p>
                <p><strong>Franchisee:</strong> <?php echo htmlspecialchars($file['business_name'] ?? 'Unknown'); ?></p>
                <p><strong>Upload Date:</strong> <?php echo date("M d, Y", strtotime($file['created_at'])); ?></p>

                <div class="mb-3">
                    <a href="<?php echo htmlspecialchars($webPath); ?>" 
                       class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a href="../staff_documents.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <!-- File Preview -->
                <div class="file-preview mt-4">
                    <h5><i class="fas fa-eye"></i> Preview</h5>
                    <?php
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $previewable = ['pdf'];
                    if (isset($error)): ?>
                        <p class="alert alert-error"><?php echo $error; ?></p>
                    <?php elseif (in_array($ext, $previewable)): ?>
                        <iframe src="<?php echo htmlspecialchars($webPath); ?>" allowfullscreen></iframe>
                    <?php else: ?>
                        <p class="alert alert-info">Only PDF files can be previewed in the browser. Non-PDF files have been automatically downloaded.</p>
                        <script>
                            // Trigger automatic download for non-PDF files
                            window.onload = function() {
                                const link = document.createElement('a');
                                link.href = '<?php echo htmlspecialchars($webPath); ?>';
                                link.download = '<?php echo htmlspecialchars($filePath); ?>';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            };
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>