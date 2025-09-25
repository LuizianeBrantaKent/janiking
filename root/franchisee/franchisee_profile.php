<?php
// franchisee/franchisee_profile.php
// Uses $conn (PDO) from db/config.php — we DO NOT change config.php.

session_start();

// ===== Only logged-in franchisees =====
if (empty($_SESSION['franchisee_id'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../../db/config.php';

// ---- adapt to your config ($conn is PDO)
if (!isset($conn) || !($conn instanceof PDO)) {
  die('Database connection not initialized. Expected $conn (PDO) from db/config.php.');
}
$pdo = $conn; // alias so rest of file uses $pdo

// ---- Brand + CSRF
$BRAND_BLUE = '#004990';
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$franchiseeId = (int)$_SESSION['franchisee_id'];

// ===== Fetch current profile =====
$stmt = $pdo->prepare("
  SELECT franchisee_id, business_name, address, abn, start_date, status,
         point_of_contact, phone, email
  FROM franchisees
  WHERE franchisee_id = :id
  LIMIT 1
");
$stmt->execute([':id' => $franchiseeId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
  http_response_code(404);
  echo "Franchisee not found.";
  exit;
}

// ===== Handle updates =====
$flash = ['type' => null, 'msg' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $flash = ['type' => 'danger', 'msg' => 'Invalid request. Please try again.'];
  } else {
    // sanitize
    $business_name    = trim($_POST['business_name'] ?? '');
    $point_of_contact = trim($_POST['point_of_contact'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $abn              = trim($_POST['abn'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // validate
    $errors = [];
    if ($business_name === '')    $errors[] = 'Business name is required.';
    if ($point_of_contact === '') $errors[] = 'Point of contact is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($new_password !== '' || $confirm_password !== '') {
      if ($new_password !== $confirm_password) $errors[] = 'Passwords do not match.';
      if ($new_password !== '' && strlen($new_password) < 8) $errors[] = 'Password must be at least 8 characters.';
    }

    if (empty($errors)) {
      try {
        $pdo->beginTransaction();

        // base update
        $u = $pdo->prepare("
          UPDATE franchisees
             SET business_name = :business_name,
                 point_of_contact = :poc,
                 address = :address,
                 abn = :abn,
                 phone = :phone,
                 email = :email
           WHERE franchisee_id = :id
           LIMIT 1
        ");
        $u->execute([
          ':business_name' => $business_name,
          ':poc'           => $point_of_contact,
          ':address'       => $address,
          ':abn'           => $abn,
          ':phone'         => $phone,
          ':email'         => $email,
          ':id'            => $franchiseeId
        ]);

        // optional password change
        if ($new_password !== '') {
          $hash = password_hash($new_password, PASSWORD_DEFAULT);
          $p = $pdo->prepare("UPDATE franchisees SET password_hash = :h WHERE franchisee_id = :id LIMIT 1");
          $p->execute([':h' => $hash, ':id' => $franchiseeId]);
        }

        $pdo->commit();
        $flash = ['type' => 'success', 'msg' => 'Profile updated successfully.'];

        // refresh
        $stmt->execute([':id' => $franchiseeId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $flash = ['type' => 'danger', 'msg' => 'Update failed. Please try again.'];
      }
    } else {
      $flash = ['type' => 'warning', 'msg' => implode(' ', $errors)];
    }
  }
}

// ===== Includes (keep your shared header/navbar) =====
require_once __DIR__ . '/../includes/franchisee_header.php';

echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />';
echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />';

echo '<link rel="stylesheet" href="../assets/css/franchisee_profile.css">';

require_once __DIR__ . '/../includes/franchisee_navbar.php';
?>

<main id="main-profile" class="main-content container-fluid">
  <div class="row">
    <div class="col-12 col-xl-10">
      <div class="d-flex align-items-center gap-2 mb-3">
        <i class="fa-solid fa-user-gear fs-4" style="color: <?= htmlspecialchars($BRAND_BLUE) ?>;"></i>
        <h1 class="h4 mb-0 page-title">Profile Settings</h1>
      </div>

      <?php if (!empty($flash['msg'])): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?>"><?= htmlspecialchars($flash['msg']) ?></div>
      <?php endif; ?>

      <!-- Summary card -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-center gap-4">
          <div class="avatar">
            <i class="fa-solid fa-building-user"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <h2 class="h5 mb-0 brand-text"><?= htmlspecialchars($profile['business_name'] ?: '—') ?></h2>
              <span class="badge badge-soft brand">
                <i class="fa-solid fa-hashtag me-1"></i>ID: <?= (int)$profile['franchisee_id'] ?>
              </span>
              <span class="badge badge-soft secondary">
                <i class="fa-solid fa-toggle-on me-1"></i><?= htmlspecialchars(ucfirst($profile['status'] ?? 'active')) ?>
              </span>
              <?php if(!empty($profile['abn'])): ?>
                <span class="badge badge-soft info">
                  <i class="fa-solid fa-id-card-clip me-1"></i>ABN: <?= htmlspecialchars($profile['abn']) ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="text-muted small mt-2">
              <i class="fa-regular fa-calendar me-1"></i>
              Started: <?= htmlspecialchars($profile['start_date'] ? date('M d, Y', strtotime($profile['start_date'])) : '—') ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
    <form method="post" class="card shadow-sm" novalidate>
        <div class="card-header bg-white"> 
        <strong class="section-title">
        <i class="fa-solid fa-pen-to-square me-2"></i>Edit Details</strong> </div> <div class="card-body"> 
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="row g-3">
  <!-- Row 1 -->
  <div class="col-lg-6 col-12">
    <label class="form-label small">Business Name</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-briefcase"></i></span>
      <input name="business_name" class="form-control" required
             value="<?= htmlspecialchars($profile['business_name'] ?? '') ?>">
    </div>
  </div>

  <div class="col-lg-6 col-12">
    <label class="form-label small">Point of Contact</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
      <input name="point_of_contact" class="form-control" required
             value="<?= htmlspecialchars($profile['point_of_contact'] ?? '') ?>">
    </div>
  </div>

  <!-- Row 2 -->
  <div class="col-lg-8 col-12">
    <label class="form-label small">Address</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
      <input name="address" class="form-control"
             value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
    </div>
  </div>

  <div class="col-lg-4 col-12">
    <label class="form-label small">ABN</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-id-card-clip"></i></span>
      <input name="abn" class="form-control"
             value="<?= htmlspecialchars($profile['abn'] ?? '') ?>">
    </div>
  </div>

  <!-- Row 3 -->
  <div class="col-lg-6 col-12">
    <label class="form-label small">Email</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
      <input type="email" name="email" class="form-control" required
             value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
    </div>
  </div>

  <div class="col-lg-6 col-12">
    <label class="form-label small">Phone</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
      <input name="phone" class="form-control"
             value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
    </div>
  </div>
</div>

<hr class="my-4">

<!-- Change password block -->
<div class="row g-3">
  <div class="col-12">
    <span class="fw-semibold"><i class="fa-solid fa-lock me-2"></i>Change Password (optional)</span>
  </div>

  <div class="col-lg-6 col-12">
    <label class="form-label small">New Password</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
      <input type="password" name="new_password" class="form-control" placeholder="••••••••">
    </div>
  </div>

  <div class="col-lg-6 col-12">
    <label class="form-label small">Confirm Password</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fa-solid fa-shield"></i></span>
      <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
    </div>
  </div>
</div>


        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
          <div class="text-muted small">
            <i class="fa-regular fa-circle-question me-1"></i>
            Need help? Contact admin.
          </div>
          <div class="d-flex gap-2">
            <a href="./franchisee_dash.php" class="btn btn-outline-secondary">
              <i class="fa-regular fa-circle-xmark me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-brand">
              <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

<?php

echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';

