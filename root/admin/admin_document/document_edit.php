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

// Fetch franchisees for selection
try {
    $franchiseeStmt = $conn->query("SELECT franchisee_id, business_name FROM franchisees ORDER BY business_name");
    $franchisees = $franchiseeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching franchisees: " . $e->getMessage();
}

// Fetch the existing file info
try {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE documents_id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        $error = "File not found!";
    }
} catch (PDOException $e) {
    $error = "Error fetching file: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $version = $_POST['version'];
    $franchisee_id = $_POST['franchisee_id'];
    $description = $category . " - v" . $version;

    // File handling
    $fileName = $file['file_path']; // Default to existing
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = "../../assets/uploads/";
        $newFileName = basename($_FILES['file']['name']);
        $fileTmp = $_FILES['file']['tmp_name'];
        $filePath = $uploadDir . $newFileName;

        // Validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $error = "Invalid file type. Only PDF, DOC, DOCX, and TXT files are allowed.";
        } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload error: " . $_FILES['file']['error'];
        } elseif (move_uploaded_file($fileTmp, $filePath)) {
            $fileName = $filePath; // Update to new file path
        } else {
            $error = "Error moving uploaded file.";
        }
    }

    if (!isset($error)) {
        try {
            $stmt = $conn->prepare("UPDATE documents SET franchisee_id = ?, title = ?, description = ?, file_path = ? WHERE documents_id = ?");
            $stmt->execute([$franchisee_id, $title, $description, $fileName, $id]);
            $success = "File updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating file: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Document</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>
  <div class="main-container">
    <main class="main-content">
      <h1 class="page-title">Edit Document</h1>
      <p class="page-subtitle">Update details of uploaded document</p>

      <!-- Messages -->
      <?php if (isset($error)): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <?php if ($file): ?>
        <div class="upload-section">
          <h2 class="section-title"><i class="fas fa-edit"></i> Edit Document</h2>
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label>File Name:</label>
              <input type="text" name="title" class="form-control" 
                     value="<?php echo htmlspecialchars($file['title']); ?>" required>
            </div>
            <div class="form-group">
              <label>Description:</label>
              <?php
              $descParts = explode(' - v', $file['description']);
              $categoryVal = $descParts[0] ?? $file['description'];
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
              <label>Franchisee (Who Can View):</label>
              <select name="franchisee_id" class="form-control" required>
                <?php foreach ($franchisees as $f): ?>
                  <option value="<?php echo $f['franchisee_id']; ?>" 
                          <?php echo $f['franchisee_id'] == $file['franchisee_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['business_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Replace File (optional):</label>
              <input type="file" name="file" class="form-control">
            </div>
            <button type="submit" name="update" class="btn btn-primary">
              <i class="fas fa-save"></i> Update
            </button>
            <a href="../admin_documents.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Back
            </a>
          </form>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
<?php ob_end_flush(); ?>