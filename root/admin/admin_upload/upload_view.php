<?php
include('../../../db/config.php'); // this defines $link

if(!isset($_GET['id'])){
    header("Location: ../admin_uploads.php");
    exit;
}

$id = $_GET['id'];
$stmt = $link->prepare("SELECT * FROM training WHERE training_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if(!$file){
    echo "File not found!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View File</title>
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
    .form-group p{padding:10px; background:#f1f1f1; border-radius:5px;}
    .btn{padding:10px 20px; border:none; border-radius:5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none;}
    .btn-primary{background:var(--brand-blue); color:white;}
    .btn-primary:hover{background:#003b73;}
  </style>
</head>
<body>
  <div class="main-container">
    <main class="main-content">
      <h1 class="page-title">View File</h1>
      <p class="page-subtitle">Details of uploaded file</p>

      <div class="upload-section">
        <h2 class="section-title"><i class="fas fa-file-alt"></i> File Details</h2>
        <div class="form-group">
          <label>Title:</label>
          <p><?php echo htmlspecialchars($file['title']); ?></p>
        </div>
        <div class="form-group">
          <label>Description:</label>
          <p><?php echo htmlspecialchars($file['description']); ?></p>
        </div>
        <div class="form-group">
          <label>Upload Date:</label>
          <p><?php echo date("M d, Y", strtotime($file['created_at'])); ?></p>
        </div>
        <div class="form-group">
        <label>File:</label>
        <a href="../../assets/uploads/<?php echo $file['file_path']; ?>" class="btn btn-primary" download>
            <i class="fas fa-download"></i> Download
        </a>
        </div>

    </main>
  </div>
</body>
</html>
