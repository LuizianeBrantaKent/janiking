<?php
// franchisee/franchisee_training_docs/view.php
session_start();

// DB config (exact path you requested)
require_once __DIR__ . '/../../../db/config.php';

// Normalize DB handle (PDO or MySQLi)
if (!isset($conn)) {
  if (isset($mysqli)) $conn = $mysqli;
  elseif (isset($con)) $conn = $con;
}

// Validate id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo "Invalid file ID."; exit; }

// Fetch file by id (simple, like admin)
$file = null;
try {
  if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare(
      "SELECT training_id, title, description, created_at, file_path
       FROM training WHERE training_id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
  } elseif ($conn instanceof mysqli) {
    $stmt = $conn->prepare(
      "SELECT training_id, title, description, created_at, file_path
       FROM training WHERE training_id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res  = $stmt->get_result();
    $file = $res ? $res->fetch_assoc() : null;
  }
} catch (Throwable $e) {
  error_log("viewer query error: ".$e->getMessage());
}

if (!$file) { echo "File not found."; exit; }

// Build preview URL: absolute if not already
$path = $file['file_path'] ?? '';
if ($path && !preg_match('~^(https?://|/)~i', $path)) {
  $path = '/assets/uploads/' . rawurlencode(basename($path));
}
$title = htmlspecialchars($file['title'] ?: 'Document', ENT_QUOTES, 'UTF-8');
$desc  = htmlspecialchars($file['description'] ?? '', ENT_QUOTES, 'UTF-8');
$date  = htmlspecialchars(date("M d, Y", strtotime($file['created_at'])), ENT_QUOTES, 'UTF-8');
$src   = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$isPDF   = ($ext === 'pdf');
$isImage = in_array($ext, ['png','jpg','jpeg','gif','webp'], true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= $title ?> – View</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/css/franchisee_training_docs.css" />
</head>
<body class="doc-viewer">
  <div class="dv-topbar">
    <h1 class="dv-title"><?= $title ?></h1>
    <div class="dv-actions">
      <a class="dv-btn" href="<?= $src ?>" download>Download</a>
    </div>
  </div>

  <div class="dv-meta">
    <?php if ($desc): ?><div><strong>Description:</strong> <?= $desc ?></div><?php endif; ?>
    <div><strong>Upload Date:</strong> <?= $date ?></div>
  </div>

  <div class="dv-body">
    <?php if ($isPDF): ?>
      <iframe class="dv-frame" src="<?= $src ?>"></iframe>
    <?php elseif ($isImage): ?>
      <img class="dv-image" src="<?= $src ?>" alt="<?= $title ?>" />
    <?php else: ?>
      <div class="dv-fallback">
        Preview not available. <a href="<?= $src ?>" target="_blank" rel="noopener">Open / download</a>.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
