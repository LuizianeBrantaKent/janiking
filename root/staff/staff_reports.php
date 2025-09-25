<?php
declare(strict_types=1);

// staff_reports.php — only Generate New Report form + CSV download
if (session_status() === PHP_SESSION_NONE) session_start();

// Require login (only allow staff role)
$currentUserId = $_SESSION['user_id'] ?? null;
$currentRole   = $_SESSION['role'] ?? null;

if (!$currentUserId || $currentRole !== 'Staff') {
    header('Location: /login.php');
    exit;
}

// DB (PDO in $conn)
require_once __DIR__ . '/../../db/config.php';  // adjust if needed
/** @var PDO $conn */
$pdo = $conn;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Helpers
if (!function_exists('e')) {
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

/* -------------------- Handle “Generate New Report” (POST) -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rtype = $_POST['report-type'] ?? '';
  $start = trim((string)($_POST['start_date'] ?? ''));
  $end   = trim((string)($_POST['end_date'] ?? ''));

  $q = ['report' => $rtype, 'export' => 'csv'];
  if ($rtype === 'bookings') {
    if ($start !== '') $q['b_from'] = $start;
    if ($end   !== '') $q['b_to']   = $end;
  } elseif ($rtype === 'franchisees') {
    if ($start !== '') $q['f_from'] = $start;
    if ($end   !== '') $q['f_to']   = $end;
  } elseif ($rtype !== 'inventory') {
    $q['report'] = 'bookings';
  }

  $url = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($q);
  header('Location: ' . $url);
  exit;
}

/* -------------------- CSV export handling -------------------- */
$report = $_GET['report'] ?? null;
$export = isset($_GET['export']) && $_GET['export'] === 'csv';

if ($export && $report) {
  $filename = "report_{$report}_" . date('Ymd_His') . ".csv";
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Pragma: no-cache'); header('Expires: 0');
  $out = fopen('php://output', 'w');

  if ($report === 'bookings') {
    fputcsv($out, ['Booking ID','Franchisee ID','Name','Email','Phone','Preferred Location','Scheduled Date','Status','Notes']);
    $stmt = $pdo->query("SELECT booking_id, franchisee_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status, notes FROM bookings");
    while ($r = $stmt->fetch()) {
      fputcsv($out, [
        $r['booking_id'], $r['franchisee_id'],
        trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')),
        $r['email'], $r['phone'], $r['preferred_location'],
        $r['scheduled_date'], $r['status'],
        preg_replace('/\s+/', ' ', (string)($r['notes'] ?? ''))
      ]);
    }
  } elseif ($report === 'inventory') {
    fputcsv($out, ['Product ID','Name','Category','Price','Stock Quantity','Description']);
    $stmt = $pdo->query("SELECT product_id, name, category, price, stock_quantity, description FROM products");
    while ($r = $stmt->fetch()) {
      fputcsv($out, [
        $r['product_id'], $r['name'], $r['category'],
        $r['price'], $r['stock_quantity'],
        preg_replace('/\s+/', ' ', (string)($r['description'] ?? ''))
      ]);
    }
  } elseif ($report === 'franchisees') {
    fputcsv($out, ['Franchisee ID','Business Name','Status','Start Date','POC','Email','Phone','ABN','Address']);
    $stmt = $pdo->query("SELECT franchisee_id, business_name, status, start_date, point_of_contact, email, phone, abn, address FROM franchisees");
    while ($r = $stmt->fetch()) {
      fputcsv($out, [
        $r['franchisee_id'], $r['business_name'], $r['status'], $r['start_date'],
        $r['point_of_contact'], $r['email'], $r['phone'], $r['abn'], $r['address']
      ]);
    }
  }

  fclose($out);
  exit;
}

$pageTitle = 'Reports';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reports – JaniKing</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body class="staff">
  <div class="app">
    <?php require_once __DIR__ . '/../includes/staff_header.php'; ?>
    <?php require_once __DIR__ . '/../includes/staff_navbar.php'; ?>

    <main class="content">
      <!-- Page title -->
      <div class="page-head">
        <h1>Reports</h1>
        <p>Generate and export database reports.</p>
      </div>

      <!-- ===== Generate New Report ONLY ===== -->
      <div class="cardx generate-section mb-4">
        <div class="card-body">
          <h2 class="section-title mb-2">Generate New Report</h2>
          <hr style="border:0; border-top:1px solid var(--line); margin: 8px 0 18px;">

          <form action="" method="POST" class="stack">
            <div class="grid" style="grid-template-columns: 1fr; gap:14px;">
              <div class="form-group">
                <label for="report-type" class="form-label">Report Type</label>
                <select class="form-control" id="report-type" name="report-type" required>
                  <option value="">Select Report Type</option>
                  <option value="bookings">Booking Report</option>
                  <option value="inventory">Inventory Report</option>
                  <option value="franchisees">Franchisee Report</option>
                </select>
              </div>

              <div class="date-range">
                <div class="form-group">
                  <label for="start-date" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="start-date" name="start_date">
                </div>
                <div class="form-group">
                  <label for="end-date" class="form-label">End Date</label>
                  <input type="date" class="form-control" id="end-date" name="end_date">
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:6px;">
              <i class="fa-solid fa-file-csv"></i>&nbsp; Generate Excel Report
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
