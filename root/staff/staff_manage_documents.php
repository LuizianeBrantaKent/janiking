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
function db(): PDO { // not used here, but kept for parity
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

$flash = null;

/* delete file */
if (isset($_GET['del'])) {
  $name = basename((string)$_GET['del']);
  $path = $UPLOAD_DIR . '/' . $name;
  if ($name !== '' && is_file($path)) {
    @unlink($path);
    $flash = "Deleted “{$name}”.";
  } else {
    $flash = "File not found.";
  }
}

/* list files */
$files = [];
$filter = $_GET['type'] ?? 'all';
$q = trim((string)($_GET['q'] ?? ''));

$finfo = function(string $p): array {
  $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
  $mime = '';
  if (function_exists('mime_content_type')) { $mime = @mime_content_type($p) ?: ''; }
  return [$ext, $mime];
};
$formatBytes = function(int $b): string {
  $u=['B','KB','MB','GB','TB']; $i=0; while($b>=1024 && $i<count($u)-1){$b/=1024;$i++;} return round($b,2).' '.$u[$i];
};

foreach (glob($UPLOAD_DIR . '/*') as $p) {
  if (!is_file($p)) continue;
  [$ext, $mime] = $finfo($p);
  $base = basename($p);
  $matchType = match ($filter) {
    'images' => str_starts_with((string)$mime, 'image/'),
    'pdf'    => $ext === 'pdf',
    'docs'   => in_array($ext, ['doc','docx','xls','xlsx','csv','txt']),
    default  => true,
  };
  $matchQ = $q === '' || stripos($base, $q) !== false;
  if ($matchType && $matchQ) {
    $files[] = [
      'name' => $base,
      'url'  => $UPLOAD_URL . '/' . rawurlencode($base),
      'size' => filesize($p),
      'mtime'=> filemtime($p),
      'ext'  => $ext,
      'mime' => $mime ?: 'application/octet-stream',
      'is_img' => str_starts_with((string)$mime, 'image/'),
      'path' => $p,
    ];
  }
}
usort($files, fn($a,$b)=> $b['mtime'] <=> $a['mtime']);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Documents – JaniKing</title>
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
      <a class="active" href="#"><i class="fa fa-folder-open"></i><span>Documents</span></a>
      <a href="<?= e(asset_url('staff/staff_upload_files.php')) ?>"><i class="fa fa-upload"></i><span>Upload Files</span></a>
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
        <h1>Documents</h1>
        <p>Browse, preview and manage uploaded files.</p>
      </div>
      <a class="btnx btnx-primary" href="<?= e(asset_url('staff/staff_upload_files.php')) ?>"><i class="fa fa-plus me-1"></i>Upload</a>
    </div>

    <?php if ($flash): ?><div class="pill badge-complete mb-2"><?= e($flash) ?></div><?php endif; ?>

    <div class="cardx mb-3">
      <div class="card-body">
        <form class="row g-2">
          <div class="col-sm-3">
            <label class="form-label">Type</label>
            <select class="form-select" name="type">
              <?php foreach (['all'=>'All','images'=>'Images','pdf'=>'PDF','docs'=>'Docs/Sheets/CSV/TXT'] as $k=>$v): ?>
                <option value="<?= e($k) ?>" <?= $filter===$k?'selected':'' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label">Search</label>
            <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Filename contains…">
          </div>
          <div class="col-sm-3 d-flex align-items-end">
            <button class="btnx btnx-primary me-2">Apply</button>
            <a class="btnx" href="<?= e(asset_url('staff/staff_manage_documents.php')) ?>">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-wrap">
      <table class="tablex">
        <thead><tr>
          <th style="width:60px">Preview</th>
          <th>Name</th>
          <th>Type</th>
          <th>Size</th>
          <th>Modified</th>
          <th style="width:220px">Actions</th>
        </tr></thead>
        <tbody>
          <?php if ($files): foreach ($files as $f): ?>
            <tr>
              <td>
                <?php if ($f['is_img']): ?>
                  <img src="<?= e($f['url']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                <?php else: ?>
                  <div class="d-inline-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:8px;background:#eef2ff;">
                    <i class="fa fa-file fa-lg" aria-hidden="true"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= e($f['name']) ?></td>
              <td><?= e($f['mime']) ?></td>
              <td><?= e($formatBytes((int)$f['size'])) ?></td>
              <td><?= e(date('Y-m-d H:i', (int)$f['mtime'])) ?></td>
              <td class="d-flex" style="gap:.5rem">
                <a class="btnx btnx-primary btn-sm" target="_blank" href="<?= e($f['url']) ?>">Open</a>
                <a class="btnx btn-sm" href="<?= e($f['url']) ?>" download>Download</a>
                <a class="btnx btn-sm" href="?del=<?= e(rawurlencode($f['name'])) ?>" onclick="return confirm('Delete this file?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="meta">No files found. Try uploading some.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
