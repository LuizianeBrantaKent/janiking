<?php
declare(strict_types=1);
ini_set('display_errors','1'); error_reporting(E_ALL);

/* ---------- tiny bootstrap (same as other pages) ---------- */
$APP_ROOT = realpath(__DIR__ . '/..');
$DOC_ROOT = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$BASE_URL = str_replace($DOC_ROOT, '', str_replace('\\','/', $APP_ROOT));
if ($BASE_URL === '' || $BASE_URL[0] !== '/') $BASE_URL = '/' . ltrim($BASE_URL, '/');

function e($s=''){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function asset_url($p){ global $BASE_URL; return rtrim($BASE_URL,'/') . '/' . ltrim($p,'/'); }
function db(): PDO { // parity only
  static $pdo; if ($pdo) return $pdo;
  $pdo=new PDO('mysql:host=127.0.0.1;dbname=janiking;charset=utf8mb4','root','',[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}

/* ---------- page data ---------- */
$USER_ID = 2;
$logo   = asset_url('assets/images/logo.png');
$avatar = asset_url('assets/images/Michael_Thompson.png');

$UPLOAD_DIR = $APP_ROOT . '/uploads';
$UPLOAD_URL = asset_url('uploads');
if (!is_dir($UPLOAD_DIR)) { @mkdir($UPLOAD_DIR, 0775, true); }

$flash = null; $results = [];

/* handle upload */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $allowed = ['png','jpg','jpeg','gif','webp','svg','pdf','doc','docx','xls','xlsx','csv','txt'];
  $maxBytes = 25 * 1024 * 1024; // 25MB per file

  if (!isset($_FILES['file'])) {
    $flash = "No files selected.";
  } else {
    $f = $_FILES['file'];
    $count = is_array($f['name']) ? count($f['name']) : 0;

    for ($i=0; $i<$count; $i++) {
      $name = (string)$f['name'][$i];
      $tmp  = (string)$f['tmp_name'][$i];
      $err  = (int)$f['error'][$i];
      $size = (int)$f['size'][$i];

      if ($err === UPLOAD_ERR_NO_FILE) continue;
      if ($err !== UPLOAD_ERR_OK) { $results[] = [$name, 'Upload failed (error '.$err.').', false]; continue; }
      if ($size > $maxBytes) { $results[] = [$name, 'File too large.', false]; continue; }

      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed, true)) { $results[] = [$name, 'Type not allowed.', false]; continue; }

      $safeBase = preg_replace('/[^A-Za-z0-9_\-\.]/','_', pathinfo($name, PATHINFO_FILENAME));
      $newName  = $safeBase . '_' . date('Ymd-His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
      $destPath = $UPLOAD_DIR . '/' . $newName;

      if (!@move_uploaded_file($tmp, $destPath)) {
        $results[] = [$name, 'Could not save file.', false]; continue;
      }
      @chmod($destPath, 0664);
      $results[] = [$name, $UPLOAD_URL . '/' . rawurlencode($newName), true];
    }

    if (!$results) {
      $flash = "Nothing was uploaded.";
    } else {
      $ok = array_sum(array_map(fn($r)=>$r[2]?1:0, $results));
      $fail = count($results) - $ok;
      $flash = "Upload finished – {$ok} succeeded, {$fail} failed.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Upload Files – JaniKing</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="<?= e(asset_url('assets/css/styles.css')) ?>">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="logo"><img src="<?= e($logo) ?>" alt="JaniKing"></div>
    <nav class="nav">
      <a href="<?= e(asset_url('staff/staff_dashboard.php')) ?>"><i class="fa fa-home"></i><span>Dashboard</span></a>
      <a href="<?= e(asset_url('staff/staff_communication.php')) ?>"><i class="fa fa-comments"></i><span>Communication</span></a>
      <a href="<?= e(asset_url('staff/staff_reports.php')) ?>"><i class="fa fa-chart-line"></i><span>Reports</span></a>
      <a href="<?= e(asset_url('staff/staff_manage_documents.php')) ?>"><i class="fa fa-folder-open"></i><span>Documents</span></a>
      <a class="active" href="#"><i class="fa fa-upload"></i><span>Upload Files</span></a>
      <a href="<?= e(asset_url('staff/staff_manage_training.php')) ?>"><i class="fa fa-graduation-cap"></i><span>Training</span></a>
      <a href="<?= e(asset_url('staff/staff_profile.php')) ?>"><i class="fa fa-user"></i><span>Profile / Settings</span></a>
    </nav>
    <div class="spacer"></div>
  </aside>

  <header class="header">
    <div class="user">
      <div class="text-end me-2"><strong>Michael Thompson</strong><small class="d-block" style="color:var(--muted)">Staff</small></div>
      <div class="avatar"><img src="<?= e($avatar) ?>" alt=""></div>
    </div>
  </header>

  <main class="content">
    <div class="page-head d-flex align-items-center justify-content-between">
      <div>
        <h1>Upload Files</h1>
        <p>Allowed: png, jpg, jpeg, gif, webp, svg, pdf, doc, docx, xls, xlsx, csv, txt (≤ 25MB each).</p>
      </div>
      <a class="btnx" href="<?= e(asset_url('staff/staff_manage_documents.php')) ?>"><i class="fa fa-folder-open me-1"></i>View documents</a>
    </div>

    <?php if ($flash): ?><div class="pill badge-complete mb-2"><?= e($flash) ?></div><?php endif; ?>

    <div class="cardx mb-3">
      <div class="card-header">Select files</div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="stack">
          <input class="form-control" type="file" name="file[]" multiple required>
          <button class="btnx btnx-primary"><i class="fa fa-upload me-1"></i>Upload</button>
        </form>
      </div>
    </div>

    <?php if ($results): ?>
      <div class="cardx">
        <div class="card-header">Results</div>
        <div class="card-body">
          <div class="listy">
            <?php foreach ($results as [$orig, $msg, $ok]): ?>
              <div class="item">
                <div><strong><?= e($orig) ?></strong></div>
                <div class="meta">
                  <?php if ($ok): ?>
                    <a href="<?= e($msg) ?>" target="_blank"><?= e($msg) ?></a>
                  <?php else: ?>
                    <?= e($msg) ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
