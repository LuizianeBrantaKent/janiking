<?php
// staff/booking/reschedule_booking.php
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
  function e(mixed $s): string {
      if ($s === null) return '';
      return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

$booking_id = (int)($_GET['id'] ?? 0);
if ($booking_id <= 0) { header('Location: /staff/staff_booking.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id=:id");
$stmt->execute([':id'=>$booking_id]);
$booking = $stmt->fetch();
if(!$booking){ header('Location: /staff/staff_booking.php'); exit; }

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $scheduled_input = trim($_POST['scheduled_date'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  if(!$scheduled_input){$errors[]="Schedule is required";}
  $scheduled_date = $scheduled_input ? date('Y-m-d H:i:00',strtotime($scheduled_input)) : null;

  if(!$errors){
    $stmt=$conn->prepare("UPDATE bookings SET scheduled_date=:sdt, notes=:nt WHERE booking_id=:id");
    $stmt->execute([':sdt'=>$scheduled_date,':nt'=>$notes,':id'=>$booking_id]);
    header('Location: /staff/staff_booking.php?rescheduled=1');exit;
  }
}
$prefill = $booking['scheduled_date'] ? date('Y-m-d\TH:i',strtotime($booking['scheduled_date'])) : '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reschedule Appointment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/staff.css">
</head>
<body class="staff">
  <div class="app">
    <?php include __DIR__ . '/../../includes/staff_header.php'; ?>
    <?php include __DIR__ . '/../../includes/staff_navbar.php'; ?>
    <main class="content">
      <h1>Reschedule Appointment #BK-<?= e($booking['booking_id']) ?></h1>
      <?php if($errors): ?><div class="alert alert-danger"><ul><?php foreach($errors as $er) echo "<li>".e($er)."</li>"; ?></ul></div><?php endif; ?>
      <form method="post" class="stack" style="gap:12px;max-width:400px;">
        <label>New Schedule
          <input type="datetime-local" name="scheduled_date" class="form-control" value="<?= e($prefill) ?>" required>
          <small class="form-text text-muted">Select the new date and time.</small>
        </label>
        <label>Notes
          <input type="text" name="notes" class="form-control" placeholder="Optional comments..." value="<?= e($booking['notes'] ?? '') ?>">
        </label>
        <button class="btn btn-primary">Confirm Reschedule</button>
        <a href="/staff/staff_booking.php" class="btn btn-secondary">Cancel</a>
      </form>
    </main>
  </div>
</body>
</html>
