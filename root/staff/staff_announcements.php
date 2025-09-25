<?php
// staff_announcements.php
require_once "../../db/config.php";

session_start();

$staff_id    = $_SESSION['user_id']   ?? 0;
$staff_type  = $_SESSION['user_type'] ?? 'User';
$staff_email = $_SESSION['email']     ?? '';

// --------------------
// POST Handling
// --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Announcement ---
    if ($_POST['action'] === 'announcement') {
        $title    = trim($_POST['title'] ?? '');
        $content  = trim($_POST['content'] ?? '');
        $audience = $_POST['audience'] ?? 'All';

        $stmt = $conn->prepare("INSERT INTO announcements (title, content, author_id, recipient_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $staff_id, $audience]);

        header("Location: ".$_SERVER['PHP_SELF']."?tab=announcements");
        exit;
    }

    // --- Message or Reply ---
    if ($_POST['action'] === 'message') {
        $subject   = trim($_POST['subject'] ?? '');
        $body      = trim($_POST['message'] ?? '');
        $recipient = $_POST['recipient'] ?? '';
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        // Insert new message
        $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_ref_id, subject, content, parent_message_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$staff_type, $staff_id, $subject, $body, $parent_id]);
        $message_id = $conn->lastInsertId();

        if ($parent_id) {
            // Copy recipients from parent
            $pstmt = $conn->prepare("SELECT receiver_type, receiver_ref_id, email_override FROM message_recipients WHERE message_id=?");
            $pstmt->execute([$parent_id]);
            $recips = $pstmt->fetchAll(PDO::FETCH_ASSOC);

            $ir = $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type, receiver_ref_id, email_override, status) VALUES (?, ?, ?, ?, 'Unread')");
            foreach ($recips as $r) {
                $ir->execute([$message_id, $r['receiver_type'], $r['receiver_ref_id'], $r['email_override']]);
            }

        } else {
            // New thread recipients
            if ($recipient === "All") {
                $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type) VALUES (?, 'All')")
                     ->execute([$message_id]);

            } elseif ($recipient === "All staff") {
                $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type) VALUES (?, 'Allstaff')")
                     ->execute([$message_id]);

            } elseif ($recipient === "All Staff") {
                $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type) VALUES (?, 'AllStaff')")
                     ->execute([$message_id]);

            } elseif ($recipient === "All Franchisee") {
                $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type) VALUES (?, 'AllFranchisee')")
                     ->execute([$message_id]);

            } elseif ($recipient === "Others" && !empty($_POST['recipient_other'])) {
                $email = trim($_POST['recipient_other']);

                $lookup = $conn->prepare("SELECT user_id AS id,'User' AS type,email FROM users WHERE email=? UNION SELECT franchisee_id,'Franchisee',email FROM franchisees WHERE email=? LIMIT 1");
                $lookup->execute([$email,$email]);
                $m = $lookup->fetch(PDO::FETCH_ASSOC);

                if ($m) {
                    $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type, receiver_ref_id, email_override) VALUES (?,?,?,?)")
                         ->execute([$message_id, $m['type'], $m['id'], $m['email']]);
                } else {
                    $conn->prepare("INSERT INTO message_recipients (message_id, receiver_type, email_override) VALUES (?,?,?)")
                         ->execute([$message_id,'Others',$email]);
                }
            }
        }

        header("Location: ".$_SERVER['PHP_SELF']."?tab=messages");
        exit;
    }

    // --- Delete Thread ---
    if ($_POST['action'] === 'delete_message') {
        $msg_id = (int)$_POST['message_id'];

        $stmt = $conn->prepare("DELETE FROM message_recipients WHERE message_id = ? OR message_id IN (SELECT message_id FROM messages WHERE parent_message_id=?)");
        $stmt->execute([$msg_id, $msg_id]);

        $stmt = $conn->prepare("DELETE FROM messages WHERE parent_message_id = ?");
        $stmt->execute([$msg_id]);

        $stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$msg_id]);

        header("Location: ".$_SERVER['PHP_SELF']."?tab=messages");
        exit;
    }
}

// --------------------
// Data Fetch
// --------------------
$activeTab = $_GET['tab'] ?? 'announcements';

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// ✅ FIXED: Only fetch threads relevant to the current user
$sql = "
    SELECT m.*, 
           COALESCE(sender.email, fsender.email) AS sender_email,
           GROUP_CONCAT(
             CASE 
               WHEN r.receiver_type='User' THEN u.email
               WHEN r.receiver_type='Franchisee' THEN f.email
               WHEN r.receiver_type='Others' THEN r.email_override
               WHEN r.receiver_type='All' THEN 'All'
               WHEN r.receiver_type='Allstaff' THEN 'All staff'
               WHEN r.receiver_type='AllStaff' THEN 'All Staff'
               WHEN r.receiver_type='AllFranchisee' THEN 'All Franchisee'
             END SEPARATOR ', '
           ) AS recipients
    FROM messages m
    JOIN message_recipients r ON m.message_id=r.message_id
    LEFT JOIN users u ON (r.receiver_type='User' AND r.receiver_ref_id=u.user_id)
    LEFT JOIN franchisees f ON (r.receiver_type='Franchisee' AND r.receiver_ref_id=f.franchisee_id)
    LEFT JOIN users sender ON (m.sender_type='User' AND m.sender_ref_id=sender.user_id)
    LEFT JOIN franchisees fsender ON (m.sender_type='Franchisee' AND m.sender_ref_id=fsender.franchisee_id)
    WHERE m.parent_message_id IS NULL
      AND (
        -- Messages I sent (outgoing)
        (m.sender_type=:staff_type AND m.sender_ref_id=:staff_id)
        
        OR 
        
        -- Messages sent specifically to me (incoming)
        (
          -- Sent to my specific email address
          (r.receiver_type='Others' AND r.email_override=:staff_email)
          
          OR
          
          -- Sent to my specific user ID
          (r.receiver_type='User' AND r.receiver_ref_id=:staff_id)
          
          OR
          
          -- Sent to my specific franchisee ID (if applicable)
          (r.receiver_type='Franchisee' AND r.receiver_ref_id=:staff_id)
        )
      )
    GROUP BY m.message_id
    ORDER BY m.sent_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute(['staff_id'=>$staff_id, 'staff_type'=>$staff_type, 'staff_email'=>$staff_email]);
$threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Communication Center</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/staff.css">
  <style>
    #suggestions {border:1px solid #ccc;background:#fff;position:absolute;top:100%;left:0;right:0;z-index:1000;max-height:150px;overflow-y:auto;display:none;}
    .suggestion{padding:5px 10px;cursor:pointer;} .suggestion:hover{background:#f0f0f0;}
    .badge-sent{background:#007bff;color:#fff;} .badge-recv{background:#28a745;color:#fff;}
    .delete-icon { color:#dc3545; cursor:pointer; }
    .delete-icon:hover { color:#a71d2a; }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('../includes/staff_header.php'); ?>
<?php include('../includes/staff_navbar.php'); ?>
<div class="main-container">
<main class="main-content">
  <h1 class="page-title">Communication</h1>

<div class="container mt-5">
  <ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link <?=($activeTab==='announcements'?'active':'')?>" href="#announcements" data-toggle="tab">Announcements</a></li>
    <li class="nav-item"><a class="nav-link <?=($activeTab==='messages'?'active':'')?>" href="#messages" data-toggle="tab">Messages</a></li>
  </ul>

  <div class="tab-content">
    <!-- Announcements -->
    <div class="tab-pane fade <?=($activeTab==='announcements'?'show active':'')?>" id="announcements">
      <form method="post" class="mt-3">
        <input type="hidden" name="action" value="announcement">
        <input type="text" name="title" class="form-control" placeholder="Title" required>
        <textarea name="content" class="form-control mt-2" placeholder="Content" required></textarea>
        <select name="audience" class="form-control mt-2">
          <option>All</option><option>Franchisee</option><option>Staff</option><option>staff</option>
        </select>
        <button class="btn btn-primary mt-2">Post</button>
      </form>
      <h4 class="mt-4">Recent Announcements</h4>
      <table class="table table-bordered">
        <tr><th>Title</th><th>Content</th><th>Audience</th><th>Date</th></tr>
        <?php foreach($announcements as $a): ?>
          <tr>
            <td><?=htmlspecialchars($a['title'])?></td>
            <td><?=htmlspecialchars($a['content'])?></td>
            <td><?=$a['recipient_type']?></td>
            <td><?=$a['created_at']?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <!-- Messages -->
    <div class="tab-pane fade <?=($activeTab==='messages'?'show active':'')?>" id="messages">
      <form method="post" class="mt-3">
        <input type="hidden" name="action" value="message">
        <select name="recipient" id="recipient" class="form-control" required>
          <option>All</option>
          <option>All staff</option>
          <option>All Staff</option>
          <option>All Franchisee</option>
          <option>Others</option>
        </select>
        <div style="position:relative;margin-top:8px;">
          <input type="text" id="recipientOther" name="recipient_other" class="form-control" placeholder="Type email..." style="display:none;">
          <div id="suggestions"></div>
        </div>
        <input type="text" name="subject" class="form-control mt-2" placeholder="Subject" required>
        <textarea name="message" class="form-control mt-2" placeholder="Message" required></textarea>
        <button class="btn btn-primary mt-2">Send</button>
      </form>

      <h4 class="mt-4">Inbox / Sent</h4>
      <div class="accordion" id="inboxAccordion">
        <?php foreach($threads as $i=>$t): ?>
          <?php $isSent = ($t['sender_type']===$staff_type && (int)$t['sender_ref_id']===$staff_id); ?>
          <div class="card">
            <div class="card-header" id="heading<?=$i?>">
              <h5 class="mb-0 d-flex justify-content-between align-items-center">
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?=$i?>">
                  <span class="<?=$isSent?'badge badge-sent':'badge badge-recv'?>"><?=$isSent?'Sent':'Received'?></span>
                  <?=htmlspecialchars($t['subject'])?> <small class="text-muted">(<?=$t['sent_at']?>)</small>
                </button>
                <div>
                  <span class="text-muted small">From: <?=htmlspecialchars($t['sender_email'] ?? 'System')?> | To: <?=$t['recipients']?></span>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="delete_message">
                    <input type="hidden" name="message_id" value="<?=$t['message_id']?>">
                    <button class="btn btn-link p-0 ml-2 delete-icon" title="Delete" onclick="return confirm('Delete this thread and all replies?')">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </div>
              </h5>
            </div>
            <div id="collapse<?=$i?>" class="collapse" data-parent="#inboxAccordion">
              <div class="card-body">
                <?=nl2br(htmlspecialchars($t['content']))?>
                <?php
                  $replies=$conn->prepare("
                    SELECT m.*, COALESCE(sender.email, fsender.email) AS sender_email,
                    GROUP_CONCAT(
                      CASE 
                        WHEN r.receiver_type='User' THEN u.email
                        WHEN r.receiver_type='Franchisee' THEN f.email
                        WHEN r.receiver_type='Others' THEN r.email_override
                        WHEN r.receiver_type='All' THEN 'All'
                        WHEN r.receiver_type='Allstaff' THEN 'All staff'
                        WHEN r.receiver_type='AllStaff' THEN 'All Staff'
                        WHEN r.receiver_type='AllFranchisee' THEN 'All Franchisee'
                      END SEPARATOR ', '
                    ) AS recipients
                    FROM messages m 
                    JOIN message_recipients r ON m.message_id=r.message_id
                    LEFT JOIN users u ON (r.receiver_type='User' AND r.receiver_ref_id=u.user_id)
                    LEFT JOIN franchisees f ON (r.receiver_type='Franchisee' AND r.receiver_ref_id=f.franchisee_id)
                    LEFT JOIN users sender ON (m.sender_type='User' AND m.sender_ref_id=sender.user_id)
                    LEFT JOIN franchisees fsender ON (m.sender_type='Franchisee' AND m.sender_ref_id=fsender.franchisee_id)
                    WHERE m.parent_message_id=? 
                    GROUP BY m.message_id 
                    ORDER BY m.sent_at ASC
                  ");
                  $replies->execute([$t['message_id']]);
                  foreach($replies->fetchAll(PDO::FETCH_ASSOC) as $r):
                    $rSent=($r['sender_type']===$staff_type && (int)$r['sender_ref_id']===$staff_id); ?>
                    <div class="ml-3 mt-3 border-left pl-3">
                      <span class="<?=$rSent?'badge badge-sent':'badge badge-recv'?>"><?=$rSent?'Sent':'Received'?></span>
                      <strong><?=$rSent?'To:':'From:'?></strong> <?=htmlspecialchars($r['sender_email'] ?? 'System')?> <br>
                      <strong>Recipients:</strong> <?=$r['recipients']?><br>
                      <strong>Re: <?=$r['subject']?></strong><br>
                      <?=nl2br(htmlspecialchars($r['content']))?>
                      <div class="text-muted small"><?=$r['sent_at']?></div>
                    </div>
                <?php endforeach; ?>
                <form method="post" class="mt-3">
                  <input type="hidden" name="action" value="message">
                  <input type="hidden" name="parent_id" value="<?=$t['message_id']?>">
                  <input type="hidden" name="subject" value="<?=htmlspecialchars($t['subject'])?>">
                  <textarea name="message" class="form-control mb-2" placeholder="Reply..." required></textarea>
                  <button class="btn btn-sm btn-secondary">Reply</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$('#recipient').on('change',function(){ 
  if($(this).val()==='Others') {
    $('#recipientOther').show(); 
  } else {
    $('#recipientOther').hide();
    $('#suggestions').hide();
  }
});

// Add email search functionality
$('#recipientOther').on('input', function() {
  const searchTerm = $(this).val();
  if (searchTerm.length > 2) {
    $.get('staff_announcement/search_contacts.php', { q: searchTerm }, function(data) {
      const suggestions = $('#suggestions');
      suggestions.empty();
      
      if (data.length > 0) {
        data.forEach(function(email) {
          suggestions.append('<div class="suggestion">' + email + '</div>');
        });
        suggestions.show();
      } else {
        suggestions.hide();
      }
    }, 'json');
  } else {
    $('#suggestions').hide();
  }
});

// Handle suggestion click
$(document).on('click', '.suggestion', function() {
  $('#recipientOther').val($(this).text());
  $('#suggestions').hide();
});

// Hide suggestions when clicking outside
$(document).on('click', function(e) {
  if (!$(e.target).closest('#recipientOther, #suggestions').length) {
    $('#suggestions').hide();
  }
});
</script>
</body>
</html>