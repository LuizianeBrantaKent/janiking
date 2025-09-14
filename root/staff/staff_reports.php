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

$status = $_GET['status'] ?? '';
$from   = $_GET['from']   ?? '';
$to     = $_GET['to']     ?? '';

$sql = "SELECT booking_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status 
        FROM bookings WHERE 1=1";
$params = [];
if ($status !== '') { $sql .= " AND status = :st"; $params[':st'] = $status; }
if ($from !== '')   { $sql .= " AND scheduled_date >= :from"; $params[':from'] = $from.' 00:00:00'; }
if ($to !== '')     { $sql .= " AND scheduled_date <= :to";   $params[':to']   = $to.' 23:59:59'; }
$sql .= " ORDER BY scheduled_date DESC LIMIT 200";

$stmt = db()->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reports – JaniKing</title>
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
      <a class="active" href="#"><i class="fa fa-chart-line"></i><span>Reports</span></a>
      <a href="<?= e(asset_url('staff/staff_manage_documents.php')) ?>"><i class="fa fa-folder-open"></i><span>Documents</span></a>
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
    <div class="page-head"><h1>Bookings Report</h1><p>Filter by status and date range.</p></div>

    <div class="cardx mb-3">
      <div class="card-body">
        <form class="row g-2">
          <div class="col-sm-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <?php foreach ([''=>'All','Pending'=>'Pending','Confirmed'=>'Confirmed','Completed'=>'Completed','Cancelled'=>'Cancelled'] as $k=>$v): ?>
                <option value="<?= e($k) ?>" <?= $status===$k?'selected':'' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-3">
            <label class="form-label">From</label>
            <input class="form-control" type="date" name="from" value="<?= e($from) ?>">
          </div>
          <div class="col-sm-3">
            <label class="form-label">To</label>
            <input class="form-control" type="date" name="to" value="<?= e($to) ?>">
          </div>
          <div class="col-sm-3 d-flex align-items-end">
            <button class="btnx btnx-primary me-2">Apply</button>
            <a class="btnx" href="<?= e(asset_url('staff/staff_reports.php')) ?>">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-wrap">
      <table class="tablex">
        <thead><tr>
          <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
          <th>Preferred Location</th><th>Scheduled</th><th>Status</th>
        </tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['booking_id']) ?></td>
              <td><?= e($r['first_name'].' '.$r['last_name']) ?></td>
              <td><?= e($r['email']) ?></td>
              <td><?= e($r['phone']) ?></td>
              <td><?= e($r['preferred_location']) ?></td>
              <td><?= e($r['scheduled_date']) ?></td>
              <td><span class="badgex"><?= e($r['status']) ?></span></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7" class="meta">No results.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
