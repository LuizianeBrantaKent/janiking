<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

require_once "../../db/config.php"; // uses $conn
include('../includes/staff_header.php');
include('../includes/staff_navbar.php');

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

$uploadDir = "../assets/uploads/";

// Fetch franchisees for selection
try {
    $franchiseeStmt = $conn->query("SELECT franchisee_id, business_name FROM franchisees ORDER BY business_name");
    $franchisees = $franchiseeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching franchisees: " . $e->getMessage();
    $franchisees = [];
}

// Helper function to get franchisee name
function getFranchiseeName($id, $franchisees) {
    foreach ($franchisees as $f) {
        if ($f['franchisee_id'] == $id) {
            return htmlspecialchars($f['business_name']);
        }
    }
    return 'Unknown';
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $franchisee_id = $_POST['franchisee_id'];
    $fileName = preg_replace('/\s+/', '_', basename($_FILES['file']['name'])); // Replace spaces with underscores
    $fileTmp = $_FILES['file']['tmp_name'];
    $filePath = $uploadDir . $fileName;

    // Validate file type (allow common document types)
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
    $fileType = $_FILES['file']['type'];
    if (!in_array($fileType, $allowedTypes)) {
        $error = "Invalid file type. Only PDF, DOC, DOCX, and TXT files are allowed.";
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error: " . $_FILES['file']['error'];
    } elseif (!is_uploaded_file($fileTmp)) {
        $error = "Invalid file upload attempt.";
    } elseif (move_uploaded_file($fileTmp, $filePath)) {
        try {
            $sql = "INSERT INTO documents (franchisee_id, title, description, file_path, created_at) VALUES (?, ?, ?, ?, NOW())";
            $description = $category;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$franchisee_id, $title, $description, $fileName]); // Store filename only
            $success = "File uploaded successfully.";
        } catch (PDOException $e) {
            $error = "Error uploading file to database: " . $e->getMessage();
        }
    } else {
        $error = "Error moving uploaded file. Check directory permissions for '$uploadDir'.";
    }
}

// Fetch all documents uploads
try {
    $stmt = $conn->query("SELECT * FROM documents ORDER BY created_at DESC");
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
  <title>Janiking - Upload Documents</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_staff.css">
</head>
<body>

<div class="main-container">
  <main class="main-content">
    <h1 class="page-title">Upload Documents</h1>
    <p class="page-subtitle">Upload documents for Franchisees</p>

    <!-- Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
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
          <label for="franchisee_id">Franchisee (Who Can View)</label>
          <select name="franchisee_id" class="form-control" required>
            <?php foreach ($franchisees as $f): ?>
              <option value="<?php echo $f['franchisee_id']; ?>"><?php echo htmlspecialchars($f['business_name']); ?></option>
            <?php endforeach; ?>
          </select>
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
      <h2 class="section-title">Recent Documents Uploaded</h2>
      <table>
        <thead>
          <tr>
            <th>File Name</th>
            <th>Description</th>
            <th>Franchisee</th>
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
              <td><?php echo getFranchiseeName($row['franchisee_id'], $franchisees); ?></td>
              <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
              <td>
                <div class="upload-actions">
                  <a href="staff_document/document_view.php?id=<?php echo $row['documents_id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i> View</a>
                  <a href="staff_document/document_edit.php?id=<?php echo $row['documents_id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                  <a href="staff_document/document_delete.php?id=<?php echo $row['documents_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this file?')"><i class="fas fa-trash"></i> Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No uploads found.</td>
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