<?php
// staff/booking/create_booking.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['staff_id']) && isset($_SESSION['user_id'])) {
  $_SESSION['staff_id'] = $_SESSION['user_id'];
}
$currentStaffId = $_SESSION['staff_id'] ?? null;
if (!$currentStaffId) { header('Location: /login.php'); exit; }

require_once __DIR__ . '/../../../db/config.php';
/** @var PDO $conn */
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if (!function_exists('e')) {
  function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

$franchisees = $conn->query("SELECT franchisee_id, business_name FROM franchisees ORDER BY business_name")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $franchisee_id = (int)($_POST['franchisee_id'] ?? 0);
  $first_name = trim((string)($_POST['first_name'] ?? ''));
  $last_name = trim((string)($_POST['last_name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $preferred_location = trim((string)($_POST['preferred_location'] ?? ''));
  $scheduled_input = trim((string)($_POST['scheduled_date'] ?? ''));
  $notes = trim((string)($_POST['notes'] ?? ''));

  if ($franchisee_id <= 0) $errors[] = 'Franchisee is required';
  if ($first_name === '') $errors[] = 'First name is required';
  if ($email === '') $errors[] = 'Email is required';
  if ($scheduled_input === '') $errors[] = 'Schedule date/time is required';

  $scheduled_date = '';
  if ($scheduled_input) {
    $ts = strtotime($scheduled_input);
    if ($ts !== false) $scheduled_date = date('Y-m-d H:i:00', $ts);
  }

  if (!$errors) {
    $stmt = $conn->prepare("INSERT INTO bookings (franchisee_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status, notes)
                            VALUES (:fid,:fn,:ln,:em,:ph,:loc,:sdt,'Pending',:notes)");
    $stmt->execute([
      ':fid'=>$franchisee_id, ':fn'=>$first_name, ':ln'=>$last_name, ':em'=>$email,
      ':ph'=>$phone, ':loc'=>$preferred_location, ':sdt'=>$scheduled_date, ':notes'=>$notes
    ]);
    header('Location: /staff/staff_booking.php?success=1'); exit;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Create Appointment – JaniKing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/staff.css">
</head>
<body class="staff">
  <div class="app">
    <?php include __DIR__ . '/../../includes/staff_header.php'; ?>
    <?php include __DIR__ . '/../../includes/staff_navbar.php'; ?>

    <main class="content">
      <div class="page-head d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div><h1 class="mb-0">New Appointment</h1></div>
        <div><a href="/staff/staff_booking.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a></div>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach($errors as $er){echo "<li>".e($er)."</li>";} ?></ul></div>
      <?php endif; ?>

      <div class="cardx"><div class="card-body">
        <form method="post" class="grid" style="grid-template-columns:repeat(2,1fr);gap:16px;">
          <div>
            <label class="form-label">Franchisee*</label>
            <select name="franchisee_id" class="form-control" required>
              <option value="">-- Select Franchisee --</option>
              <?php foreach($franchisees as $f): ?>
                <option value="<?= (int)$f['franchisee_id'] ?>"><?= e($f['business_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Choose which franchisee this booking belongs to.</small>
          </div>
          <div>
            <label class="form-label">Location</label>
            <input type="text" name="preferred_location" class="form-control" placeholder="Enter preferred location">
          </div>
          <div>
            <label class="form-label">First Name*</label>
            <input type="text" name="first_name" class="form-control" placeholder="e.g. John" required>
          </div>
          <div>
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" placeholder="e.g. Smith">
          </div>
          <div>
            <label class="form-label">Email*</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
          </div>
          <div>
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 0412 345 678">
          </div>
          <div>
            <label class="form-label">Schedule*</label>
            <input type="datetime-local" name="scheduled_date" class="form-control" required>
            <small class="form-text text-muted">Pick the booking date and time.</small>
          </div>
          <div>
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="Optional notes">
          </div>
          <div style="grid-column:1/-1"><button class="btn btn-primary">Create Appointment</button></div>
        </form>
      </div></div>
    </main>
  </div>
</body>
</html>
