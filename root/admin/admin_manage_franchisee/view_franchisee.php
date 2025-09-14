<?php
require_once "../../../db/config.php";
if (!isset($_GET['id'])) die("Invalid request");
$id = intval($_GET['id']);

$sql = "SELECT * FROM franchisees WHERE franchisee_id=?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$franchisee = mysqli_fetch_assoc($result);
if (!$franchisee) die("Franchisee not found.");
?>
<!DOCTYPE html>
<html>
<head><title>View Franchisee</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head>
<body class="container mt-5">
<h2>Franchisee Details</h2>
<ul class="list-group">
  <li class="list-group-item"><strong>ID:</strong> #FR-<?php echo $franchisee['franchisee_id']; ?></li>
  <li class="list-group-item"><strong>Business Name:</strong> <?php echo htmlspecialchars($franchisee['business_name']); ?></li>
  <li class="list-group-item"><strong>Address:</strong> <?php echo htmlspecialchars($franchisee['address']); ?></li>
  <li class="list-group-item"><strong>ABN:</strong> <?php echo htmlspecialchars($franchisee['abn']); ?></li>
  <li class="list-group-item"><strong>Start Date:</strong> <?php echo htmlspecialchars($franchisee['start_date']); ?></li>
  <li class="list-group-item"><strong>Status:</strong> <?php echo htmlspecialchars($franchisee['status']); ?></li>
  <li class="list-group-item"><strong>Point of Contact:</strong> <?php echo htmlspecialchars($franchisee['point_of_contact']); ?></li>
  <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($franchisee['phone']); ?></li>
  <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($franchisee['email']); ?></li>
</ul>
<a href="../admin_manage_franchisee.php" class="btn btn-secondary mt-3">Back</a>
</body></html>
