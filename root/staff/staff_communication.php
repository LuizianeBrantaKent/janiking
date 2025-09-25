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

$USER_ID = 2;
$logo = asset_url('assets/images/logo.png');
$avatar = asset_url('assets/images/Michael_Thompson.png');

$pdo = db();
$flash = null;

// --- handle new announcement ---
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form'] ?? '')==='announcement') {
  $subject = trim((string)($_POST['subject'] ?? ''));
  $content = trim((string)($_POST['content'] ?? ''));
  if ($content !== '') {
    $stmt=$pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, status, is_announcement) VALUES (:sid, NULL, :subj, :body, 'Unread', 1)");
    $stmt->execute([':sid'=>$USER_ID, ':subj'=>$subject?:null, ':body'=>$content]);
    $flash = "Announcement posted.";
  } else { $flash = "Message cannot be empty."; }
}

// --- handle new direct message ---
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form'] ?? '')==='direct') {
  $to = (int)($_POST['to_user'] ?? 0);
  $subject = trim((string)($_POST['subject'] ?? ''));
  $content = trim((string)($_POST['content'] ?? ''));
  if ($to && $content !== '') {
    $stmt=$pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, status, is_announcement) VALUES (:sid, :rid, :subj, :body, 'Unread', 0)");
    $stmt->execute([':sid'=>$USER_ID, ':rid'=>$to, ':subj'=>$subject?:null, ':body'=>$content]);
    $flash = "Message sent.";
  } else { $flash = "Choose a recipient and write a message."; }
}

// --- fetch lists ---
$users = $pdo->query("SELECT user_id, name, role FROM users ORDER BY name")->fetchAll();
$ann = $pdo->query("SELECT message_id, subject, content, sent_at, status FROM messages WHERE is_announcement=1 ORDER BY sent_at DESC LIMIT 20")->fetchAll();
$inbox = $pdo->query("SELECT message_id, sender_id, subject, content, sent_at, status 
                      FROM messages 
                      WHERE receiver_id={$USER_ID} AND is_announcement=0 
                      ORDER BY sent_at DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Communication – JaniKing</title>
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
      <a class="active" href="#"><i class="fa fa-comments"></i><span>Communication</span></a>
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
    <div class="page-head"><h1>Communication</h1><p>Announcements and direct messages.</p></div>
    <?php if ($flash): ?><div class="pill badge-complete mb-2"><?= e($flash) ?></div><?php endif; ?>

    <div class="tabs mb-3">
      <a class="tab active" href="#ann" onclick="showTab('ann');return false;">Announcements</a>
      <a class="tab" href="#dm" onclick="showTab('dm');return false;">Direct Messages</a>
      <a class="tab" href="#compose" onclick="showTab('compose');return false;">Compose</a>
    </div>

    <div id="ann" class="cardx">
      <div class="card-header">Latest Announcements</div>
      <div class="card-body">
        <div class="listy">
          <?php if ($ann): foreach ($ann as $m): ?>
            <div class="item">
              <div>
                <h4><?= e($m['subject'] ?: 'Announcement') ?></h4>
                <p><?= e($m['content']) ?></p>
                <small><?= e(date('Y-m-d H:i', strtotime($m['sent_at']))) ?> • <span class="status-chip status-<?= strtolower((string)$m['status']) ?>"><?= e($m['status']) ?></span></small>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="meta">Nothing posted yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="dm" class="cardx" style="display:none">
      <div class="card-header">Your Inbox</div>
      <div class="card-body">
        <div class="listy">
          <?php if ($inbox): foreach ($inbox as $m): ?>
            <div class="item">
              <div>
                <h4><?= e($m['subject'] ?: 'No subject') ?></h4>
                <p><?= e($m['content']) ?></p>
                <small><?= e(date('Y-m-d H:i', strtotime($m['sent_at']))) ?> • <span class="status-chip"><?= e($m['status']) ?></span></small>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="meta">No messages.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="compose" class="cardx mt-3" style="display:none">
      <div class="card-header">Compose</div>
      <div class="card-body">
        <div class="row2">
          <form method="post" class="cardx">
            <div class="card-header">New Announcement</div>
            <div class="card-body">
              <input type="hidden" name="form" value="announcement">
              <div class="mb-2">
                <label class="form-label">Subject (optional)</label>
                <input class="form-control" name="subject" type="text" maxlength="200">
              </div>
              <div class="mb-2">
                <label class="form-label">Content</label>
                <textarea class="form-control" name="content" required></textarea>
              </div>
              <button class="btnx btnx-primary">Post</button>
            </div>
          </form>

          <form method="post" class="cardx">
            <div class="card-header">Direct Message</div>
            <div class="card-body">
              <input type="hidden" name="form" value="direct">
              <div class="mb-2">
                <label class="form-label">To</label>
                <select class="form-select" name="to_user" required>
                  <option value="">Select user…</option>
                  <?php foreach ($users as $u): if ((int)$u['user_id']===$USER_ID) continue; ?>
                    <option value="<?= e($u['user_id']) ?>"><?= e($u['name'].' ('.$u['role'].')') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Subject (optional)</label>
                <input class="form-control" name="subject" type="text" maxlength="200">
              </div>
              <div class="mb-2">
                <label class="form-label">Message</label>
                <textarea class="form-control" name="content" required></textarea>
              </div>
              <button class="btnx btnx-primary">Send</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<script>
function showTab(id){
  for(const el of document.querySelectorAll('#ann,#dm,#compose')) el.style.display='none';
  document.getElementById(id).style.display='';
  for(const t of document.querySelectorAll('.tab')) t.classList.remove('active');
  const map = {ann:0, dm:1, compose:2}; document.querySelectorAll('.tab')[map[id]].classList.add('active');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
