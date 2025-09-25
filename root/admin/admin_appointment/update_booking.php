<?php
require_once "../../../db/config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval($_GET["id"]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $status = $_POST["status"];
        try {
            $sql = "UPDATE bookings SET status=? WHERE booking_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $id]);
            header("location: ../admin_manage_appointments.php?updated=1");
            exit;
        } catch (PDOException $e) {
            $error = "Error updating booking: " . $e->getMessage();
        }
    }

    $sql = "SELECT * FROM bookings WHERE booking_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) {
        header("location: ../admin_manage_appointments.php");
        exit;
    }
} else {
    header("location: ../admin_manage_appointments.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
    <main class="main-content">
    <h1 class="page-title">Update Booking Status (#<?php echo $booking['booking_id']; ?>)</h1>
    <form method="post">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="Pending"   <?php if($booking['status']=="Pending") echo "selected"; ?>>Pending</option>
                <option value="Confirmed" <?php if($booking['status']=="Confirmed") echo "selected"; ?>>Confirmed</option>
                <option value="Completed" <?php if($booking['status']=="Completed") echo "selected"; ?>>Completed</option>
                <option value="Cancelled" <?php if($booking['status']=="Cancelled") echo "selected"; ?>>Cancelled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="../admin_manage_appointments.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</main>
</body>
</html>
