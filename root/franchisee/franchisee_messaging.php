<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* 1) Load your config */
require_once __DIR__ . '/../../db/config.php';   // <-- correct path from /root/franchisee/

/* 2) Guarantee a PDO handle even if config.php didn't set $pdo */
if (!isset($pdo) || !($pdo instanceof PDO)) {
  try {
    // XAMPP defaults; change db/user/pass if yours differ
    $pdo = new PDO(
      'mysql:host=127.0.0.1;dbname=janiking;charset=utf8mb4',
      'root',
      '',
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
    );
  } catch (Throwable $e) {
    http_response_code(500);
    echo 'DB connect failed: ' . htmlspecialchars($e->getMessage());
    exit;
  }
}

/* ---------- PRG: handle SEND before any HTML ---------- */
$tabGet = $_GET['tab'] ?? 'dm';
if ($tabGet === 'dm'
    && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && ( $_POST['action'] ?? '' ) === 'send_message') {

  $to        = (int)($_POST['to_user_id'] ?? 0);
  $sub       = trim($_POST['subject'] ?? '');
  $txt       = trim($_POST['content'] ?? '');
  $currentId = (int)($_POST['current_message_id'] ?? 0); // keep selected message

  if ($to && $sub !== '' && $txt !== '') {
    // Franchisee -> Admin/Staff: sender_id is NULL (franchisee side), receiver is user
    $stmt = $pdo->prepare(
      "INSERT INTO messages (sender_id, receiver_id, subject, content, status)
       VALUES (NULL, :rid, :s, :b, 'Unread')"
    ); // sent_at has a DEFAULT in your schema
    $stmt->execute([':rid'=>$to, ':s'=>$sub, ':b'=>$txt]);
  }

  header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=dm&id=' . $currentId, true, 303);
  exit;
}

/* ---------- PAGE STATE ---------- */
$tab = ($tabGet === 'ann') ? 'ann' : 'dm';
$id  = (int)($_GET['id'] ?? 0);

/* ---------- Directory of Admin/Staff (recipients) ---------- */
$users = $pdo->query("
  SELECT user_id, role, name
  FROM users
  WHERE status='Active'
  ORDER BY FIELD(role,'Admin','Staff'), name
")->fetchAll();

/* ---------- Lists ---------- */
if ($tab === 'ann') {
  // Announcements list
  $list = $pdo->query("
    SELECT a.announcement_id AS id, a.title, a.content, a.created_at, u.name AS author_name
    FROM announcements a
    LEFT JOIN users u ON u.user_id = a.author_id
    ORDER BY a.created_at DESC
  ")->fetchAll();
} else {
  // --- Direct Messages list: only real rows, most recent first ---
  $MSG_LIMIT = 4; // tweak how many to show in the list

  $sql = <<<SQL
  SELECT
    m.message_id AS id,
    m.subject, m.content, m.sent_at, m.status,
    m.sender_id, m.receiver_id,
    us.name AS sender_name, us.role AS sender_role,
    ur.name AS receiver_name, ur.role AS receiver_role
  FROM messages m
  LEFT JOIN users us ON us.user_id = m.sender_id
  LEFT JOIN users ur ON ur.user_id = m.receiver_id
  WHERE
    (
        (m.sender_id IS NULL AND m.receiver_id IS NOT NULL)
     OR (m.sender_id IS NOT NULL AND m.receiver_id IS NULL)
    )
    -- require at least subject OR content to be non-empty
    AND COALESCE(NULLIF(TRIM(m.subject), ''), NULLIF(TRIM(m.content), '')) IS NOT NULL
  ORDER BY m.sent_at DESC, m.message_id DESC
  LIMIT :lim
  SQL;

  $st = $pdo->prepare($sql);
  $st->bindValue(':lim', $MSG_LIMIT, PDO::PARAM_INT);
  $st->execute();
  $list = $st->fetchAll();

  // Auto-select first message on DM if not specified
  if (!$id && !empty($list)) $id = (int)$list[0]['id'];
}
/* ---------- View (selected) ---------- */
$view = null;
if ($id) {
  if ($tab === 'ann') {
    $st = $pdo->prepare("
      SELECT a.*, u.name AS author_name
      FROM announcements a
      LEFT JOIN users u ON u.user_id = a.author_id
      WHERE a.announcement_id = :id
    ");
    $st->execute([':id'=>$id]);
    $view = $st->fetch();
  } else {
    $st = $pdo->prepare("
      SELECT m.*,
             us.name AS sender_name, us.role AS sender_role,
             ur.name AS receiver_name, ur.role AS receiver_role
      FROM messages m
      LEFT JOIN users us ON us.user_id = m.sender_id
      LEFT JOIN users ur ON ur.user_id = m.receiver_id
      WHERE m.message_id = :id
    ");
    $st->execute([':id'=>$id]);
    $view = $st->fetch();

    if ($view && ($view['status'] ?? '') !== 'Read') {
      $pdo->prepare('UPDATE messages SET status="Read" WHERE message_id=:id')
          ->execute([':id'=>$id]);
    }
  }
}

/* ---------- Reply-to default ---------- */
$replyTo = 0;
if ($tab === 'dm' && $view) {
  // If franchisee sent it (sender_id NULL) reply to receiver; else reply to sender
  $replyTo = empty($view['sender_id']) ? (int)($view['receiver_id'] ?? 0)
                                       : (int)($view['sender_id'] ?? 0);
}
if ($tab === 'dm' && $replyTo <= 0 && !empty($users)) $replyTo = (int)$users[0]['user_id'];

/* ---------- Helpers ---------- */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function excerpt($t,$n=120){ $t=trim(strip_tags((string)$t)); return mb_strlen($t)>$n?mb_substr($t,0,$n-1).'…':$t; }

/* ---------- Layout ---------- */
require_once __DIR__ . '/../includes/franchisee_header.php';
echo '<link rel="stylesheet" href="../assets/css/franchisee_messaging.css">';
require_once __DIR__ . '/../includes/franchisee_navbar.php';
?>

<div class="page-scroll-shell">

<div id="fr-msg" class="container <?= $tab==='dm' ? 'is-dm' : '' ?>">
  <h1 class="page-title">Messages</h1>

  <div class="fr-tabs">
    <a class="fr-tab <?= $tab==='ann'?'active':'' ?>" href="?tab=ann">Announcements</a>
    <a class="fr-tab <?= $tab==='dm'?'active':'' ?>" href="?tab=dm">Direct Messages</a>
  </div>

  <div class="fr-layout">
    <!-- here starts the list -->
    <div class="fr-list">
      <?php if ($tab === 'dm'): ?>
  <header class="fr-card-head"><h1>List of Messages</h1></header>
<?php else: ?>
  <header class="fr-card-head"><h1>List of Announcements</h1></header>
<?php endif; ?>
      <?php if ($tab==='ann'): ?>
        <?php foreach ($list as $a): ?>
          <a class="fr-item <?= ($id===$a['id']?'active':'') ?>" href="?tab=ann&id=<?= (int)$a['id'] ?>">
            <div class="fr-subj"><?= e($a['title']) ?></div>
            <div class="fr-meta">
              <?= e($a['author_name'] ?: 'System') ?> •
              <?= !empty($a['created_at']) ? date('M d, Y H:i', strtotime($a['created_at'])) : '' ?>
            </div>
            <div class="fr-prev"><?= e(excerpt($a['content'])) ?></div>
          </a>
        <?php endforeach; ?>
        <?php if (empty($list)): ?><div class="fr-item">No announcements yet.</div><?php endif; ?>
      <?php else: ?>
        <?php foreach ($list as $m): ?>
          <?php $toLbl = $m['receiver_name'] ? ($m['receiver_role'].' - '.$m['receiver_name']) : 'You'; ?>
          <a class="fr-item <?= ($id===$m['id']?'active':'') ?>" href="?tab=dm&id=<?= (int)$m['id'] ?>">
            <div class="fr-subj"><?= e($m['subject'] ?: '(no subject)') ?></div>
            <div class="fr-meta">
              To: <?= e($toLbl) ?>
              <?php if (!empty($m['sent_at'])): ?> • <?= date('M d, Y H:i', strtotime($m['sent_at'])) ?><?php endif; ?>
              <?php if (isset($m['status'])): ?> • <?= e($m['status']) ?><?php endif; ?>
            </div>
            <div class="fr-prev"><?= e(excerpt($m['content'])) ?></div>
          </a>
        <?php endforeach; ?>
        <?php if (empty($list)): ?><div class="fr-item">No messages yet.</div><?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Here starts the reader -->
    <div class="fr-view">
      <?php if ($tab === 'dm'): ?>
  <header class="fr-card-head"><h1>Read Your Messages</h1></header>
<?php else: ?>
  <header class="fr-card-head"><h1>Read Your Announcements</h1></header>
<?php endif; ?>
      <?php if ($tab==='ann'): ?>
        <?php if ($view): ?>
          <h2><?= e($view['title']) ?></h2>
          <div class="fr-meta">
            <?php $who=$view['author_name'] ?: 'System';
                  $when=!empty($view['created_at']) ? date('M d, Y H:i', strtotime($view['created_at'])) : ''; ?>
            By <?= e($who) ?> · <?= $when ?>
          </div>
          <div class="fr-body"><?= nl2br(e($view['content'])) ?></div>
        <?php else: ?>
          <div class="fr-note">Select an announcement to read it here.</div>
        <?php endif; ?>
      <?php else: ?>
        <?php if ($view): ?>
          <h2><?= e($view['subject'] ?: '(no subject)') ?></h2>
          <div class="fr-meta">
            <?php
              $from = $view['sender_id'] ? ($view['sender_name'].' ('.$view['sender_role'].')') : 'You';
              $to   = $view['receiver_id'] ? ($view['receiver_name'].' ('.$view['receiver_role'].')') : 'You';
              $when = !empty($view['sent_at']) ? date('M d, Y H:i', strtotime($view['sent_at'])) : '';
            ?>
            From: <?= e($from) ?> · To: <?= e($to) ?> · <?= $when ?>
          </div>
          <div class="fr-body"><?= nl2br(e($view['content'])) ?></div>
        <?php else: ?>
          <div class="fr-note">Select a message to read it here.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div><!-- /.fr-layout -->

  <?php if ($tab==='dm'): ?>
   <!-- here starts composer -->
    <form method="post"
          action="<?= e($_SERVER['PHP_SELF'] . '?tab=dm&id=' . (int)$id) ?>"
          class="fr-composer">
          <header class="fr-card-head"><h1>Reply / Send a Message</h1></header>
      <input type="hidden" name="action" value="send_message">
      <input type="hidden" name="current_message_id" value="<?= (int)$id ?>">

      <div class="rowline">
        <label class="lbl">Reply to:</label>
        <select name="to_user_id" required>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['user_id'] ?>" <?= ((int)$u['user_id']===$replyTo ? 'selected':'') ?>>
              <?= e($u['role'].' - '.$u['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="rowline">
        <input type="text" name="subject" placeholder="Subject"
               value="<?= $view ? e('Re: '.($view['subject'] ?? '')) : '' ?>" required>
      </div>

      <div class="rowline">
        <textarea name="content" placeholder="Write your message..." required></textarea>
      </div>

      <div class="rowline actions">
        <button type="submit">Send</button>
      </div>
    </form>
  <?php endif; ?>
</div>
</div><!-- /.page-scroll-shell -->

<script src="../assets/js/franchisee.js"></script>
