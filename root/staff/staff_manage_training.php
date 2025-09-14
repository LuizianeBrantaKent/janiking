<?php
declare(strict_types=1);
ini_set('display_errors','1'); error_reporting(E_ALL);

$APP_ROOT = realpath(__DIR__ . '/..');
$DOC_ROOT = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$BASE_URL = str_replace($DOC_ROOT, '', str_replace('\\','/', $APP_ROOT));
if ($BASE_URL === '' || $BASE_URL[0] !== '/') $BASE_URL = '/' . ltrim($BASE_URL, '/');

function e($s=''){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function asset_url($p){ global $BASE_URL; return rtrim($BASE_URL,'/') . '/' . ltrim($p,'/'); }
function db(): PDO {
  static $pdo; if ($pdo) return $pdo;
  $pdo=new PDO('mysql:host=127.0.0.1;dbname=janiking;charset=utf8mb4','root','',[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}

$logo = asset_url('assets/images/logo.png');
$avatar = asset_url('assets/images/Michael_Thompson.png');

$pdo = db();
$flash = null;

// Create training (simple – file path typed or paste a URL to a file uploaded via Upload Files)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='create') {
  $title = trim((string)($_POST['title']??''));
  $desc  = trim((string)($_POST['description']??''));
  $file  = trim((string)($_POST['file_path']??''));
  if ($title && $file) {
    $stmt=$pdo->prepare("INSERT INTO training (franchisee_id, title, description, file_path) VALUES (NULL, :t, :d, :f)");
    $stmt->execute([':t'=>$title, ':d'=>$desc?:null, ':f'=>$file]);
    $flash = "Training created.";
  } else { $flash = "Title and file path are required."; }
}

// Delete training
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  $pdo->prepare("DELETE FROM training WHERE training_id=:id")->execute([':id'=>$id]);
  $pdo->prepare("DELETE FROM training_acknowledgements WHERE training_id=:id")->execute([':id'=>$id]);
  $flash = "Training deleted.";
}

$rows = $pdo->query("SELECT t.training_id, t.title, t.description, t.file_path, t.created_at,
                            COALESCE(SUM(a.status='Acknowledged'),0) AS acked,
                            COUNT(a.acknowledgement_id) AS total
                     FROM training t
                     LEFT JOIN training_acknowledgements a ON a.training_id=t.training_id
                     GROUP BY t.training_id
                     ORDER BY t.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Training – JaniKing</title>
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
      <a href="<?= e(asset_url('staff/staff_upload_files.php')) ?>"><i class="fa fa-upload"></i><span>Upload Files</span></a>
      <a class="active" href="#"><i class="fa fa-graduation-cap"></i><span>Training</span></a>
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
    <div class="page-head"><h1>Training</h1><p>Create items and track acknowledgements.</p></div>
    <?php if ($flash): ?><div class="pill badge-complete mb-2"><?= e($flash) ?></div><?php endif; ?>

    <div class="cardx mb-3">
      <div class="card-header">Create Training</div>
      <div class="card-body">
        <form method="post" class="row g-2">
          <input type="hidden" name="form" value="create">
          <div class="col-sm-4">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" required>
          </div>
          <div class="col-sm-5">
            <label class="form-label">File path / URL</label>
            <input class="form-control" name="file_path" placeholder="e.g. /JaniKing/uploads/safety_manual.pdf" required>
          </div>
          <div class="col-sm-12">
            <label class="form-label">Description (optional)</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          <div class="col-sm-12">
            <button class="btnx btnx-primary">Create</button>
          </div>
        </form>
      </div>
    </div>

    <div class="table-wrap">
      <table class="tablex">
        <thead><tr><th>Title</th><th>File</th><th>Created</th><th>Acknowledged</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['title']) ?></td>
              <td><a target="_blank" href="<?= e($r['file_path']) ?>"><?= e($r['file_path']) ?></a></td>
              <td><?= e(date('Y-m-d', strtotime($r['created_at']))) ?></td>
              <td>
                <span class="badgex"><?= (int)$r['acked'] ?>/<?= (int)$r['total'] ?></span>
              </td>
              <td>
                <a class="btnx btnx-primary btn-sm" target="_blank" href="<?= e($r['file_path']) ?>">Open</a>
                <a class="btnx btn-sm" href="?del=<?= e($r['training_id']) ?>" onclick="return confirm('Delete this training?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="meta">No training yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
