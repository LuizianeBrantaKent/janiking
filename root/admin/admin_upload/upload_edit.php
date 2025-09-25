<?php
// upload_edit.php
require_once "../../../db/config.php"; // uses $conn (PDO)

if (!isset($_GET['id'])) {
    header("Location: ../admin_uploads.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch the existing file info
try {
    $stmt = $conn->prepare("SELECT * FROM training WHERE training_id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        echo "File not found!";
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching file: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $version = $_POST['version'];
    $description = $category . " - v" . $version;

    // File handling
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = "../../assets/uploads/"; 
        $fileName = basename($_FILES['file']['name']);
        $fileTmp = $_FILES['file']['tmp_name'];
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($fileTmp, $filePath)) {
            die("Error uploading file.");
        }
    } else {
        $fileName = $file['file_path']; // keep old file
    }

    try {
        $stmt = $conn->prepare("UPDATE training SET title = ?, description = ?, file_path = ? WHERE training_id = ?");
        $stmt->execute([$title, $description, $fileName, $id]);

        header("Location: ../admin_uploads.php");
        exit;
    } catch (PDOException $e) {
        die("Error updating file: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit File</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
      <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>
  <div class="main-container">
    <main class="main-content">
      <h1 class="page-title">Edit File</h1>
      <p class="page-subtitle">Update details of uploaded file</p>

      <div class="upload-section">
        <h2 class="section-title"><i class="fas fa-edit"></i> Edit File</h2>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>File Name:</label>
            <input type="text" name="title" class="form-control" 
                   value="<?php echo htmlspecialchars($file['title']); ?>" required>
          </div>
          <div class="form-group">
            <label>Decription:</label>
            <?php
            $descParts = explode(' - v', $file['description']);
            $categoryVal = $descParts[0] ?? '';
            $versionVal = $descParts[1] ?? '1.0';
            ?>
            <input type="text" name="category" class="form-control" 
                   value="<?php echo htmlspecialchars($categoryVal); ?>" required>
          </div>
          <div class="form-group">
            <label>Version:</label>
            <input type="text" name="version" class="form-control" 
                   value="<?php echo htmlspecialchars($versionVal); ?>" required>
          </div>
          <div class="form-group">
            <label>Replace File (optional):</label>
            <input type="file" name="file" class="form-control">
          </div>
          <button type="submit" name="update" class="btn btn-primary">
            <i class="fas fa-save"></i> Update
          </button>
          <a href="../admin_uploads.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
