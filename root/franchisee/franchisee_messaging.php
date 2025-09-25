
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['franchisee_id'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../../db/config.php';

// always define it early
$franchiseeId = (int)$_SESSION['franchisee_id'];

/* 2) Guarantee a PDO handle even if config.php didn’t set $pdo */
if (!isset($pdo) || !($pdo instanceof PDO)) {
  try {
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

// ================= HANDLE SEND MESSAGE =================
if (($_POST['action'] ?? '') === 'send_message') {
    $actorType = 'Franchisee';                              // since this is the franchisee portal
    $actorId   = (int)($_SESSION['franchisee_id'] ?? 0);    // adjust if session key differs

    $toUserId  = (int)($_POST['to_user_id'] ?? 0);
    $subject   = trim($_POST['subject'] ?? '');
    $content   = trim($_POST['content'] ?? '');
    $parentId  = isset($_POST['current_message_id']) ? (int)$_POST['current_message_id'] : null;

    if ($actorId && $toUserId && $subject !== '' && $content !== '') {
        $pdo->beginTransaction();
        try {
            // Insert into messages
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_type, sender_ref_id, subject, content, parent_message_id)
                VALUES (:stype, :sid, :subj, :body, :parent)
            ");
            $stmt->execute([
                'stype'  => $actorType,
                'sid'    => $actorId,
                'subj'   => $subject,
                'body'   => $content,
                'parent' => $parentId ?: null
            ]);
            $msgId = (int)$pdo->lastInsertId();

            // Insert into recipients (sending to User)
            $stmt = $pdo->prepare("
                INSERT INTO message_recipients (message_id, receiver_type, receiver_ref_id, status)
                VALUES (:mid, 'User', :rid, 'Unread')
            ");
            $stmt->execute([
                'mid' => $msgId,
                'rid' => $toUserId
            ]);

            $pdo->commit();

            // Redirect back to view the thread
            header("Location: ".$_SERVER['PHP_SELF']."?tab=dm&id=".$msgId);
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('DM send failed: '.$e->getMessage());
        }
    }
}

// =============== BUILD MESSAGE LIST for the logged-in franchisee ===============
$actorType = 'Franchisee';
$actorId   = (int)($_SESSION['franchisee_id'] ?? 0);
$id        = (int)($_GET['id'] ?? 0); // for active highlight

$sql = "
-- Sent by me
SELECT 
  m.message_id AS id,
  m.subject,
  m.content,
  m.sent_at,
  mr.status,
  CASE mr.receiver_type
    WHEN 'User'       THEN CONCAT('User - ',       COALESCE(u_to.name, '—'))
    WHEN 'Franchisee' THEN CONCAT('Franchisee - ', COALESCE(f_to.business_name, '—'))
    WHEN 'Contact'    THEN CONCAT('Contact - ',    COALESCE(ci_to.email, '—'))
    ELSE mr.receiver_type
  END AS receiver_name,
  mr.receiver_type AS receiver_role
FROM messages m
JOIN message_recipients mr ON mr.message_id = m.message_id
LEFT JOIN users u_to              ON mr.receiver_type='User'       AND mr.receiver_ref_id=u_to.user_id
LEFT JOIN franchisees f_to        ON mr.receiver_type='Franchisee' AND mr.receiver_ref_id=f_to.franchisee_id
LEFT JOIN contact_inquiries ci_to ON mr.receiver_type='Contact'    AND mr.receiver_ref_id=ci_to.id
WHERE m.sender_type=:atype1 AND m.sender_ref_id=:aid1

UNION ALL

-- Received by me
SELECT
  m.message_id AS id,
  m.subject,
  m.content,
  m.sent_at,
  mr.status,
  CASE m.sender_type
    WHEN 'User'       THEN CONCAT('User - ',       COALESCE(u_from.name, '—'))
    WHEN 'Franchisee' THEN CONCAT('Franchisee - ', COALESCE(f_from.business_name, '—'))
    WHEN 'Contact'    THEN CONCAT('Contact - ',    COALESCE(ci_from.email, '—'))
    ELSE m.sender_type
  END AS receiver_name,
  m.sender_type AS receiver_role
FROM messages m
JOIN message_recipients mr ON mr.message_id = m.message_id
LEFT JOIN users u_from              ON m.sender_type='User'       AND m.sender_ref_id=u_from.user_id
LEFT JOIN franchisees f_from        ON m.sender_type='Franchisee' AND m.sender_ref_id=f_from.franchisee_id
LEFT JOIN contact_inquiries ci_from ON m.sender_type='Contact'    AND m.sender_ref_id=ci_from.id
WHERE mr.receiver_type=:atype2 AND mr.receiver_ref_id=:aid2

ORDER BY sent_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  'atype1' => $actorType, 'aid1' => $actorId,
  'atype2' => $actorType, 'aid2' => $actorId
]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
// ===============================================================================

/* ---------- Handle SEND before output ---------- */
$tabGet = $_GET['tab'] ?? 'dm';
if ($tabGet === 'dm'
    && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && ($_POST['action'] ?? '') === 'send_message') {

  $to        = (int)($_POST['to_user_id'] ?? 0);
  $sub       = trim($_POST['subject'] ?? '');
  $txt       = trim($_POST['content'] ?? '');
  $currentId = (int)($_POST['current_message_id'] ?? 0); // keep selected message

 if ($to && $sub !== '' && $txt !== '') {
  // Franchisee -> Admin/User (receiver is a user id from the select)
  $pdo->beginTransaction();
  try {
    // 1) create the message
 $stmt = $pdo->prepare("
  INSERT INTO messages (sender_type, sender_ref_id, subject, content, parent_message_id)
  VALUES ('Franchisee', :sid, :subj, :body, :parent)
");
$stmt->execute([
  ':sid'    => $franchiseeId,     // <-- now always set
  ':subj'   => $sub,
  ':body'   => $txt,
  ':parent' => ($currentId > 0 ? $currentId : null),
]);
    $mid = (int)$pdo->lastInsertId();

    // 2) add recipient
    $stmt = $pdo->prepare("
      INSERT INTO message_recipients (message_id, receiver_type, receiver_ref_id, status)
      VALUES (:mid, 'User', :rid, 'Unread')
    ");
    $stmt->execute([
      ':mid' => $mid,
      ':rid' => $to,
    ]);

    $pdo->commit();

    // redirect to view the new message
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=dm&id=' . $mid);
    exit;
  } catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
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
  // Only announcements for All + Franchisee
  $list = $pdo->query("
    SELECT a.announcement_id AS id, a.title, a.content, a.created_at, u.name AS author_name
    FROM announcements a
    LEFT JOIN users u ON u.user_id = a.author_id
    WHERE a.recipient_type IN ('All','Franchisee')
    ORDER BY a.created_at DESC
  ")->fetchAll();
} else {
    $MSG_LIMIT = 10;

    $sql = <<<SQL
SELECT DISTINCT
  m.message_id        AS id,
  m.subject,
  m.content,
  m.sent_at,
  m.parent_message_id,
  m.sender_type,
  m.sender_ref_id
FROM messages m
JOIN message_recipients r
  ON r.message_id = m.message_id
WHERE
  (
    (r.receiver_type = 'Franchisee' AND r.receiver_ref_id = :fid)  -- messages sent TO this franchisee
    OR (m.sender_type = 'Franchisee' AND m.sender_ref_id = :fid)   -- messages sent BY this franchisee
    OR (r.receiver_type IN ('All','AllFranchisee'))                -- broadcasts
  )
  AND COALESCE(NULLIF(TRIM(m.subject), ''), NULLIF(TRIM(m.content), '')) IS NOT NULL
ORDER BY m.sent_at DESC, m.message_id DESC
LIMIT :lim
SQL;

    $st = $pdo->prepare($sql);
    $st->bindValue(':fid', $franchiseeId, PDO::PARAM_INT);
    $st->bindValue(':lim', $MSG_LIMIT, PDO::PARAM_INT);
    $st->execute();
    $list = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$id && !empty($list)) {
        $id = (int)$list[0]['id'];
    }
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
  SELECT m.*, r.receiver_type, r.receiver_ref_id, r.status AS recipient_status
  FROM messages m
  LEFT JOIN message_recipients r ON r.message_id = m.message_id
  WHERE m.message_id = :id
  LIMIT 1
");
    $st->execute([':id'=>$id]);
    $view = $st->fetch();

    // Mark as read when opened
   if ($view && ($view['recipient_status'] ?? '') !== 'Read') {
    $pdo->prepare("
        UPDATE message_recipients
           SET status = 'Read'
         WHERE message_id = :mid
           AND receiver_type = 'Franchisee'
           AND receiver_ref_id = :fid
         LIMIT 1
    ")->execute([
        ':mid' => $id,
        ':fid' => $franchiseeId
    ]);
}

  }
}

/* ---------- Reply-to default ---------- */
$replyTo = 0;
if ($tab === 'dm' && $view) {
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
<div id="fr-msg" class="container <?= $tab==='dm' ? 'is-dm' : 'is-ann' ?>">

  <h1 class="page-title">Messages</h1>

  <div class="fr-tabs">
    <a class="fr-tab <?= $tab==='ann'?'active':'' ?>" href="?tab=ann">Announcements</a>
    <a class="fr-tab <?= $tab==='dm'?'active':'' ?>" href="?tab=dm">Direct Messages</a>
  </div>

  <div class="fr-layout">
    <!-- left column: list -->
    <div class="fr-list">
      <?php if ($tab === 'dm'): ?>
        <header class="fr-card-head"><h1>List of Messages</h1></header>


        <?php foreach ($list as $m): ?>
  <?php $toLbl = !empty($m['receiver_name']) ? $m['receiver_name'] : 'You'; ?>
  <a class="fr-item <?= ((int)$id === (int)$m['id'] ? 'active' : '') ?>" href="?tab=dm&id=<?= (int)$m['id'] ?>">
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


      <?php else: ?>
        <header class="fr-card-head"><h1>List of Announcements</h1></header>
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
      <?php endif; ?>
    </div>

    <!-- right column: reader -->
    <div class="fr-view">
      <?php if ($tab === 'dm'): ?>
        <header class="fr-card-head"><h1>Read Your Messages</h1></header>
        <?php if ($view): ?>
          <h2><?= e($view['subject'] ?: '(no subject)') ?></h2>
          <div class="fr-meta">
            <?php
// WHO SENT IT
if (!empty($view['sender_name'])) {
  $from = $view['sender_name'] . (!empty($view['sender_role']) ? ' ('.$view['sender_role'].')' : '');
} else {
  // fallback: show sender_type or “System”
  $from = !empty($view['sender_type']) ? $view['sender_type'] : 'System';
}

// WHO RECEIVED IT (broadcasts vs direct)
if (!empty($view['receiver_type']) && in_array($view['receiver_type'], ['All','AllFranchisee'], true)) {
  $to = ($view['receiver_type'] === 'AllFranchisee') ? 'All Franchisees' : 'All';
} elseif (!empty($view['receiver_name'])) {
  $to = $view['receiver_name'] . (!empty($view['receiver_role']) ? ' ('.$view['receiver_role'].')' : '');
} else {
  // if the row has no explicit recipient (edge case), fallback to role/type
  $to = !empty($view['receiver_type']) ? $view['receiver_type'] : 'Recipient';
}

// WHEN
$when = !empty($view['sent_at']) ? date('M d, Y H:i', strtotime($view['sent_at'])) : '';
?>
From: <?= e($from) ?> · To: <?= e($to) ?> · <?= $when ?>

          </div>
          <div class="fr-body"><?= nl2br(e($view['content'])) ?></div>
        <?php else: ?>
          <div class="fr-note">Select a message to read it here.</div>
        <?php endif; ?>
      <?php else: ?>
        <header class="fr-card-head"><h1>Read Your Announcements</h1></header>
        <?php if ($view): ?>
          <h2><?= e($view['title']) ?></h2>
          <div class="fr-meta">
            By <?= e($view['author_name'] ?: 'System') ?> ·
            <?= !empty($view['created_at']) ? date('M d, Y H:i', strtotime($view['created_at'])) : '' ?>
          </div>
          <div class="fr-body"><?= nl2br(e($view['content'])) ?></div>
        <?php else: ?>
          <div class="fr-note">Select an announcement to read it here.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div><!-- /.fr-layout -->

  <?php
// determine mode (reply vs new)
$isReply = ($tab === 'dm' && !empty($view));
$replyTo = $replyTo ?? null; // keep your existing logic if you set this earlier

// helper: build a safe prefilled subject for replies
$prefillSubject = '';
if ($isReply && !empty($view['subject'])) {
    $s = trim($view['subject']);
    $prefillSubject = (stripos($s, 're:') === 0) ? $s : 'Re: ' . $s;
}
?>
    <!-- Reply composer -->
    <form method="post" action="<?= e($_SERVER['PHP_SELF']) . '?tab=dm&id=' . (int)$id ?>" class="fr-composer">
    <input type="hidden" name="action" value="send_message">
  <header class="fr-card-head"><h1><?= $isReply ? 'Reply' : 'New Message' ?></h1></header>

  <input type="hidden" name="action" value="send_message">
  <?php if ($isReply): ?>
    <input type="hidden" name="current_message_id" value="<?= (int)$id ?>">
  <?php endif; ?>

  <div class="rowline">
    <label class="lbl">Send to:</label>
    <select name="to_user_id" required>
      <?php foreach ($users as $u): ?>
        <option value="<?= (int)$u['user_id'] ?>"
          <?= ($replyTo && (int)$u['user_id'] === (int)$replyTo ? 'selected' : '') ?>>
          <?= e($u['role'] . ' - ' . $u['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="rowline">
    <input type="text" name="subject" placeholder="Subject"
           value="<?= $isReply ? e($prefillSubject) : '' ?>" required>
  </div>

  <div class="rowline">
    <textarea name="content" placeholder="Write your reply..." required></textarea>
  </div>

  <div class="rowline actions">
    <button type="submit" class="btn btn-secondary">Send Reply</button>
  </div>
</form>
</div>
</div><!-- /.page-scroll-shell -->

<script src="../assets/js/franchisee.js"></script>
