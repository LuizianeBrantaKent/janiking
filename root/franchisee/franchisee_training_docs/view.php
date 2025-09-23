<?php
// franchisee/franchisee_training_docs/view.php
session_start();

// DB config (same path you used)
require_once __DIR__ . '/../../../db/config.php';

// Normalize DB handle (PDO or MySQLi), keeping your existing variables
if (!isset($conn)) {
  if (isset($mysqli)) $conn = $mysqli;
  elseif (isset($con)) $conn = $con;
}

// ----- Input params -----
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? 'training'; // default keeps your training page working

if ($id <= 0) { http_response_code(400); echo "Invalid file ID."; exit; }

// Whitelist tables to avoid SQL injection via $type
if ($type === 'documents') {
  $table = 'documents';
  $pk    = 'documents_id';
} else {
  $table = 'training';
  $pk    = 'training_id';
}

// ----- Fetch record -----
$file = null;
try {
  if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("
      SELECT $pk, title, description, created_at, file_path
      FROM $table
      WHERE $pk = :id
      LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
  } elseif ($conn instanceof mysqli) {
    $stmt = $conn->prepare("
      SELECT $pk, title, description, created_at, file_path
      FROM $table
      WHERE $pk = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res  = $stmt->get_result();
    $file = $res ? $res->fetch_assoc() : null;
  } else {
    throw new Exception('No database connection handle found.');
  }
} catch (Throwable $e) {
  error_log("viewer query error: " . $e->getMessage());
}

if (!$file) { http_response_code(404); echo "File not found."; exit; }

// ----- Build absolute/served path (keeps your original logic) -----
$path = $file['file_path'] ?? '';
if ($path && !preg_match('~^(https?://|/)~i', $path)) {
  // Assume files live under /assets/uploads (adjust if needed)
  $path = '/assets/uploads/' . rawurlencode(basename($path));
}

// ----- Safe display fields -----
$title = htmlspecialchars($file['title'] ?: 'Document', ENT_QUOTES, 'UTF-8');
$desc  = htmlspecialchars($file['description'] ?? '', ENT_QUOTES, 'UTF-8');
$date  = htmlspecialchars(date("M d, Y", strtotime($file['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8');
$src   = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');

// ----- File type helpers -----
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$isPDF   = ($ext === 'pdf');
$isImage = in_array($ext, ['png','jpg','jpeg','gif','webp'], true);

// Pick an icon for the header
function faIconForExt($ext) {
  switch ($ext) {
    case 'pdf':  return 'fa-file-pdf';
    case 'doc':
    case 'docx': return 'fa-file-word';
    case 'xls':
    case 'xlsx': return 'fa-file-excel';
    case 'ppt':
    case 'pptx': return 'fa-file-powerpoint';
    case 'zip':
    case 'rar':  return 'fa-file-zipper';
    case 'png': case 'jpg': case 'jpeg': case 'gif': case 'webp':
      return 'fa-file-image';
    default:     return 'fa-file';
  }
}
$icon = faIconForExt($ext);
$badgeText = ($type === 'documents') ? 'Document' : 'Training';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $title ?> • <?= ucfirst($badgeText) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">

  <style>
    :root{
      --brand: #004990; /* your brand blue */
    }
    .text-brand{ color: var(--brand) !important; }
    .bg-brand{ background: var(--brand) !important; }
    .btn-brand{
      --bs-btn-color:#fff;
      --bs-btn-bg:var(--brand);
      --bs-btn-border-color:var(--brand);
      --bs-btn-hover-color:#fff;
      --bs-btn-hover-bg:#003b74;
      --bs-btn-hover-border-color:#003b74;
      --bs-btn-active-bg:#00345f;
      --bs-btn-active-border-color:#00345f;
    }
    .card-brand{
      border: 1px solid rgba(0,0,0,.06);
      box-shadow: 0 8px 24px rgba(0,0,0,.06);
      border-radius: 16px;
    }
    .badge-soft{
      background: rgba(0,73,144,.1);
      color: var(--brand);
      border: 1px solid rgba(0,73,144,.2);
    }
    .file-toolbar{
      gap: .5rem;
    }
    .file-frame{
      width: 100%;
      height: 80vh;
      border: 1px solid rgba(0,0,0,.1);
      border-radius: 12px;
    }
    .file-image{
      max-height: 80vh;
      object-fit: contain;
      border-radius: 12px;
      border: 1px solid rgba(0,0,0,.1);
      width: 100%;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="bg-brand text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
          <i class="fa-solid <?= $icon ?>"></i>
        </div>
        <div>
          <h1 class="h4 mb-1"><?= $title ?></h1>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <span class="badge badge-soft rounded-pill"><?= $badgeText ?></span>
            <span><i class="fa-regular fa-calendar"></i> <?= $date ?></span>
            <?php if ($ext): ?>
              <span class="text-uppercase"><i class="fa-regular fa-file"></i> <?= htmlspecialchars($ext, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="file-toolbar d-flex">
        <?php if ($src): ?>
          <a class="btn btn-brand" href="<?= $src ?>" target="_blank" rel="noopener">
            <i class="fa-solid fa-up-right-from-square me-1"></i> Open
          </a>
          <a class="btn btn-outline-secondary" href="<?= $src ?>" download>
            <i class="fa-solid fa-download me-1"></i> Download
          </a>
        <?php endif; ?>
        <button class="btn btn-outline-secondary" onclick="history.back()">
          <i class="fa-solid fa-arrow-left-long me-1"></i> Back
        </button>
      </div>
    </div>

    <!-- Body -->
    <div class="card card-brand">
      <div class="card-body">
        <?php if (!empty($desc)): ?>
          <p class="mb-4"><?= $desc ?></p>
        <?php endif; ?>

        <?php if ($src): ?>
          <?php if ($isPDF): ?>
            <iframe class="file-frame" src="<?= $src ?>"></iframe>
          <?php elseif ($isImage): ?>
            <img class="file-image" src="<?= $src ?>" alt="<?= $title ?>">
          <?php else: ?>
            <div class="text-center py-5">
              <div class="display-6 text-brand mb-2"><i class="fa-solid <?= $icon ?>"></i></div>
              <p class="mb-3">Preview not available for this file type.</p>
              <a class="btn btn-brand" href="<?= $src ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-eye me-1"></i> Open
              </a>
              <a class="btn btn-outline-secondary ms-2" href="<?= $src ?>" download>
                <i class="fa-solid fa-download me-1"></i> Download
              </a>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="alert alert-warning mb-0">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            File path is missing or invalid.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Footer/Hint -->
    <div class="text-center text-muted small mt-3">
      <i class="fa-regular fa-circle-question"></i>
      If the file does not load, try the <strong>Open</strong> button.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
