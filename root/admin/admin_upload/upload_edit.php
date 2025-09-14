<?php
include('../../../db/config.php');

if(!isset($_GET['id'])){
    header("Location: ../admin_uploads.php");
    exit;
}

$id = $_GET['id'];

// Fetch the existing file info
$stmt = $link->prepare("SELECT * FROM training WHERE training_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if(!$file){
    echo "File not found!";
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])){
    $title = $_POST['title'];
    $category = $_POST['category'];
    $version = $_POST['version'];
    $description = $category . " - v" . $version;

    // If a new file is uploaded, move it
    if(!empty($_FILES['file']['name'])){
        $uploadDir = "../assets/uploads/";
        $fileName = $_FILES['file']['name'];
        $fileTmp = $_FILES['file']['tmp_name'];
        $filePath = $uploadDir . basename($fileName);
        move_uploaded_file($fileTmp, $filePath);
    } else {
        $fileName = $file['file_path']; // keep old file
    }

    // Update database
    $stmt = $link->prepare("UPDATE training SET title = ?, description = ?, file_path = ? WHERE training_id = ?");
    $stmt->bind_param("sssi", $title, $description, $fileName, $id);
    $stmt->execute();

    header("Location: ../admin_uploads.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit File</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{
      --brand-blue: #004990;
      --bg-gray: #e9ecef;
    }
    *{margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
    body{background: var(--bg-gray); color:#333;}
    .main-container{display:flex; padding-top:80px;}
    .main-content{flex:1; padding:30px;}
    .page-title{color:var(--brand-blue); font-size:28px; margin-bottom:10px;}
    .page-subtitle{color:#6c757d; margin-bottom:30px;}
    .upload-section{background:white; padding:25px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.08);}
    .section-title{color:var(--brand-blue); font-size:20px; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #eee; display:flex; align-items:center; gap:10px;}
    .form-group{margin-bottom:20px;}
    .form-group label{display:block; font-weight:600; color:#495057; margin-bottom:8px;}
    .form-control{width:100%; padding:10px; border:1px solid #ced4da; border-radius:5px;}
    .btn{padding:10px 20px; border:none; border-radius:5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none;}
    .btn-primary{background:var(--brand-blue); color:white;}
    .btn-primary:hover{background:#003b73;}
  </style>
</head>
<body>
  <div class="main-container">
    <main class="main-content">
      <h1 class="page-title">Edit File</h1>
      <p class="page-subtitle">Update details of uploaded file</p>

      <div class="upload-section">
        <h2 class="section-title"><i class="fas fa-edit"></i> Edit File</h2>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($file['title']); ?>" required>
          </div>
          <div class="form-group">
            <label>Category:</label>
            <?php
            // extract category from description if possible
            $descParts = explode(' - v', $file['description']);
            $categoryVal = $descParts[0] ?? '';
            $versionVal = $descParts[1] ?? '1.0';
            ?>
            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($categoryVal); ?>" required>
          </div>
          <div class="form-group">
            <label>Version:</label>
            <input type="text" name="version" class="form-control" value="<?php echo htmlspecialchars($versionVal); ?>" required>
          </div>
          <div class="form-group">
            <label>Replace File (optional):</label>
            <input type="file" name="file" class="form-control">
          </div>
          <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
          <a href="../admin_uploads.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
