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
$logo   = asset_url('assets/images/logo.png');
$avatar = asset_url('assets/images/Michael_Thompson.png');

$pdo = db();
$flash = null;

/* Load current user */
$u = $pdo->prepare("SELECT user_id, role, name, email, phone, status, created_at, updated_at FROM users WHERE user_id=:id");
$u->execute([':id'=>$USER_ID]);
$user = $u->fetch();

/* Update profile */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='profile') {
  $name  = trim((string)($_POST['name']??''));
  $email = trim((string)($_POST['email']??''));
  $phone = trim((string)($_POST['phone']??''));
  if ($name && $email) {
    $upd=$pdo->prepare("UPDATE users SET name=:n, email=:e, phone=:p WHERE user_id=:id");
    $upd->execute([':n'=>$name, ':e'=>$email, ':p'=>$phone?:null, ':id'=>$USER_ID]);
    $flash="Profile updated.";
    $u->execute([':id'=>$USER_ID]); $user=$u->fetch();
  } else { $flash="Name and email are required."; }
}

/* Change password */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='password') {
  $pw1 = (string)($_POST['pw1']??'');
  $pw2 = (string)($_POST['pw2']??'');
  if ($pw1 !== '' && $pw1 === $pw2) {
    $hash = password_hash($pw1, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password_hash=:h WHERE user_id=:id")->execute([':h'=>$hash, ':id'=>$USER_ID]);
    $flash="Password changed.";
  } else { $flash="Passwords must match and not be empty."; }
}
include('../includes/staff_header.php');
include('../includes/staff_navbar.php');
?>


<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profile / Settings – JaniKing</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body class="staff" data-page="profile">


  <!-- Content -->
  <main class="content">
    <div class="page-head"><h1>Profile / Settings</h1><p>Manage your details and password.</p></div>
    <?php if ($flash): ?><div class="pill badge-ok mb-2"><?= e($flash) ?></div><?php endif; ?>

    <div class="grid profile">
      <!-- Left column: profile form -->
      <div class="cardx">
        <div class="card-header">Your details</div>
        <div class="card-body">
          <form method="post" class="stack">
            <input type="hidden" name="form" value="profile">
            <div class="field">
              <label class="form-label">Name</label>
              <input class="form-control" name="name" value="<?= e($user['name'] ?? '') ?>" required>
            </div>
            <div class="field">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
            </div>
            <div class="field">
              <label class="form-label">Phone</label>
              <input class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>">
            </div>
            <button class="btn btn-primary" type="submit">Save changes</button>
          </form>
        </div>
      </div>

      <!-- Right column: password + account -->
      <div class="stack">
        <div class="cardx">
          <div class="card-header">Change password</div>
          <div class="card-body">
            <form method="post" class="stack">
              <input type="hidden" name="form" value="password">
              <div class="field">
                <label class="form-label">New password</label>
                <input class="form-control" type="password" name="pw1" required>
              </div>
              <div class="field">
                <label class="form-label">Confirm password</label>
                <input class="form-control" type="password" name="pw2" required>
              </div>
              <button class="btn btn-primary" type="submit">Update password</button>
            </form>
          </div>
        </div>

        <div class="cardx">
          <div class="card-header">Account</div>
          <div class="card-body">
            <div class="itemline"><span>Status</span><span class="badgex"><?= e($user['status'] ?? '') ?></span></div>
            <div class="itemline"><span>Member since</span><span><?= e(isset($user['created_at'])?date('Y-m-d', strtotime($user['created_at'])):'') ?></span></div>
            <div class="itemline"><span>Last updated</span><span><?= e(isset($user['updated_at'])?date('Y-m-d', strtotime($user['updated_at'])):'') ?></span></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
