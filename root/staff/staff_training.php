<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

require_once "../../db/config.php"; // uses $conn
include('../includes/staff_header.php');
include('../includes/staff_navbar.php');

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

$uploadDir = "../assets/uploads/";

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $filePath = $uploadDir . basename($fileName);

    // Check for upload errors
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error: " . $_FILES['file']['error'];
    } elseif (!is_uploaded_file($fileTmp)) {
        $error = "Invalid file upload attempt.";
    } elseif (move_uploaded_file($fileTmp, $filePath)) {
        try {
            $sql = "INSERT INTO training (franchisee_id, title, description, file_path, created_at) VALUES (?, ?, ?, ?, NOW())";
            $franchisee_id = 1;
            $description = $category;

            $stmt = $conn->prepare($sql);
            $stmt->execute([$franchisee_id, $title, $description, $fileName]);
            $success = "File uploaded successfully.";
        } catch (PDOException $e) {
            $error = "Error uploading file to database: " . $e->getMessage();
        }
    } else {
        $error = "Error moving uploaded file. Check directory permissions.";
    }
}

// Fetch all training uploads
try {
    $stmt = $conn->query("SELECT * FROM training ORDER BY created_at DESC");
    $uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching uploads: " . $e->getMessage();
    $uploads = [];
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Upload Training Files</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>

<div class="main-container">
  <main class="main-content">
    <h1 class="page-title">Upload Training Files</h1>
    <p class="page-subtitle">Upload training materials for the Franchisees</p>

    <!-- Messages -->
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="upload-section">
      <h2 class="section-title"><i class="fas fa-cloud-upload-alt"></i> Upload File</h2>
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <input type="text" name="category" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="file">Choose File</label>
          <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
      </form>
    </div>

    <!-- Recent Uploads Table -->
    <div class="uploads-table">
      <h2 class="section-title">Recent Uploads</h2>
      <table>
        <thead>
          <tr>
            <th>File Name</th>
            <th>Description</th>
            <th>Upload Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($uploads) > 0): ?>
          <?php foreach ($uploads as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo htmlspecialchars($row['description']); ?></td>
              <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
              <td>
                <div class="upload-actions">
                  <a href="staff_training/training_view.php?id=<?php echo $row['training_id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i> View</a>
                  <a href="staff_training/training_edit.php?id=<?php echo $row['training_id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                  <a href="staff_training/training_delete.php?id=<?php echo $row['training_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this file?')"><i class="fas fa-trash"></i> Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">No uploads found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

  <!-- External JS -->
  <script src="../assets/js/script_staff.js"></script>

</body>
</html>