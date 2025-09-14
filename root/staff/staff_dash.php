<?php
declare(strict_types=1);
ini_set('display_errors','1'); error_reporting(E_ALL);

// ---- tiny bootstrap (no includes) ----
$APP_ROOT = realpath(__DIR__ . '/..');
$DOC_ROOT = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$BASE_URL = str_replace($DOC_ROOT, '', str_replace('\\','/', $APP_ROOT));
if ($BASE_URL === '' || $BASE_URL[0] !== '/') $BASE_URL = '/' . ltrim($BASE_URL, '/');

function e($s=''){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function asset_url($p){ global $BASE_URL; return rtrim($BASE_URL,'/') . '/' . ltrim($p,'/'); }

function db(): PDO {
  static $pdo=null; if ($pdo) return $pdo;
  $pdo=new PDO('mysql:host=127.0.0.1;dbname=janiking;charset=utf8mb4','root','',[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}

// ---- current user (hard-coded Staff Test) ----
$USER_ID = 2;

// ---- KPIs ----
$pdo = db();
$kpi = [
  'franchisees' => (int)$pdo->query("SELECT COUNT(*) FROM franchisees")->fetchColumn(),
  'bookings_pending' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn(),
  'unread_msgs' => (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE (receiver_id={$USER_ID} OR is_announcement=1) AND status='Unread'")->fetchColumn(),
];
$recentAnnouncements = $pdo->query("SELECT message_id, subject, content, sent_at 
                                    FROM messages 
                                    WHERE is_announcement=1 
                                    ORDER BY sent_at DESC LIMIT 6")->fetchAll();

$logo = asset_url('assets/images/logo.png');
$avatar = asset_url('assets/images/Michael_Thompson.png');
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard – JaniKing</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="<?= e(asset_url('assets/css/styles.css')) ?>">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="logo"><img src="<?= e($logo) ?>" alt="JaniKing"></div>
    <nav class="nav">
      <a class="active" href="#"><i class="fa fa-home"></i><span>Dashboard</span></a>
      <a href="<?= e(asset_url('staff/staff_communication.php')) ?>"><i class="fa fa-comments"></i><span>Communication</span></a>
      <a href="<?= e(asset_url('staff/staff_reports.php')) ?>"><i class="fa fa-chart-line"></i><span>Reports</span></a>
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
    <div class="page-head"><h1>Dashboard</h1><p>Quick view of what needs your attention.</p></div>

    <div class="grid">
      <div class="cardx kpi-card">
        <div class="kpi-left">
          <div class="kpi-ico"><i class="fa fa-users"></i></div>
          <div>
            <div class="kpi-val"><?= e($kpi['franchisees']) ?></div>
            <div class="kpi-lbl">Active Franchisees</div>
          </div>
        </div>
      </div>

      <div class="cardx kpi-card">
        <div class="kpi-left">
          <div class="kpi-ico"><i class="fa fa-calendar-check"></i></div>
          <div>
            <div class="kpi-val"><?= e($kpi['bookings_pending']) ?></div>
            <div class="kpi-lbl">Pending Bookings</div>
          </div>
        </div>
      </div>

      <div class="cardx kpi-card">
        <div class="kpi-left">
          <div class="kpi-ico"><i class="fa fa-envelope-open-text"></i></div>
          <div>
            <div class="kpi-val"><?= e($kpi['unread_msgs']) ?></div>
            <div class="kpi-lbl">Unread Messages</div>
          </div>
        </div>
      </div>
    </div>

    <div class="cardx mt-3">
      <div class="card-header">Recent announcements</div>
      <div class="card-body">
        <div class="listy">
          <?php if ($recentAnnouncements): foreach ($recentAnnouncements as $a): ?>
            <div class="list-item">
              <div>
                <strong><?= e($a['subject'] ?: 'Announcement') ?></strong>
                <div class="meta"><?= e(date('Y-m-d H:i', strtotime($a['sent_at']))) ?></div>
              </div>
              <div class="meta"><?= e(mb_strimwidth((string)$a['content'], 0, 120, '…')) ?></div>
            </div>
          <?php endforeach; else: ?>
            <div class="meta">No announcements yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
