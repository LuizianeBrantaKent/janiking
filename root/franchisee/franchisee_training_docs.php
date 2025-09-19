<?php
// ------------------ bootstrap ------------------
session_start();
require_once __DIR__ . '/../../db/config.php';                  // must expose either $conn (PDO) or $mysqli/$con (MySQLi)

include_once("../includes/franchisee_header.php");
include_once("../includes/franchisee_navbar.php");

// Load page CSS after portal CSS (spacing, colors, etc.)
echo '<link rel="stylesheet" href="../assets/css/franchisee_training_docs.css">';

// Normalise the DB handle name so we can support PDO or MySQLi
if (!isset($conn)) {
  if (isset($mysqli)) $conn = $mysqli;
  elseif (isset($con)) $conn = $con;
}

// ------------------ query: show ALL training rows ------------------
$rows = [];
$TRAINING_BASE_URL = '../uploads/';     // change if your PDFs live elsewhere

try {
  if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("
      SELECT training_id, title, description, created_at, file_path
      FROM training
      ORDER BY created_at DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<!-- driver=PDO rows=' . count($rows) . ' -->';

  } elseif ($conn instanceof mysqli) {
    $sql = "
      SELECT training_id, title, description, created_at, file_path
      FROM training
      ORDER BY created_at DESC
    ";
    $res  = $conn->query($sql);
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    echo '<!-- driver=MySQLi rows=' . count($rows) . ' -->';

  } elseif (is_object($conn) && method_exists($conn, 'query')) {
    // last-resort generic driver
    $res  = @$conn->query("
      SELECT training_id, title, description, created_at, file_path
      FROM training
      ORDER BY created_at DESC
    ");
    if ($res && method_exists($res, 'fetch_all')) {
      $rows = $res->fetch_all(MYSQLI_ASSOC);
    }
    echo '<!-- driver=Unknown rows=' . count($rows) . ' -->';
  } else {
    echo '<!-- no db handle found -->';
  }
} catch (Throwable $e) {
  error_log('Training list error: ' . $e->getMessage());
  // $rows stays [] so table shows the empty state
}
?>

<!-- ------------------ page content ------------------ -->
<main class="main-content fr-training-docs">
  <div class="uploads-table card">
    <h2 class="section-title">Training &amp; Documents</h2>

    <table class="table custom-table">
      <thead>
        <tr>
          <th>File Name</th>
          <th>Description</th>
          <th>Upload Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($rows)): ?>
        <?php foreach ($rows as $row):
              // Make a working link even if file_path is just "doc.pdf"
              $href = $row['file_path'] ?? '';
              if ($href && !preg_match('~^(https?://|/)~i', $href)) {
                $href = $TRAINING_BASE_URL . ltrim($href, '/');
              }
        ?>
          <tr>
            <td><?= htmlspecialchars($row['title'] ?? '—'); ?></td>
            <td><?= htmlspecialchars($row['description'] ?? ''); ?></td>
            <td><?= htmlspecialchars(date("M d, Y", strtotime($row['created_at']))); ?></td>
            <td>
              <div class="upload-actions">
               <?php
                $VIEWER = '/franchisee/franchisee_training_docs/view.php'; // avoid typos
                ?>
                <a href="<?= $VIEWER ?>?id=<?= (int)$row['training_id'] ?>"
                class="action-btn view-btn btn-view js-view-doc" data-viewer="popup">
                <i class="fas fa-eye"></i> View
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">No training documents found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<script src="../assets/js/franchisee.js"></script>
