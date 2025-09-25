<?php
require_once "../../../db/config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval($_GET["id"]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $scheduled_date = $_POST["scheduled_date"];
        try {
            $sql = "UPDATE bookings SET scheduled_date=? WHERE booking_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$scheduled_date, $id]);
            header("location: ../admin_manage_appointments.php?rescheduled=1");
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
    <title>Reschedule Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="container mt-5">
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>
    <div class="main-container">
    <main class="main-content">
    <h2>Reschedule Booking (#<?php echo $booking['booking_id']; ?>)</h2>
    <form method="post">
        <div class="form-group">
            <label>New Date & Time</label>
            <input type="datetime-local" name="scheduled_date" class="form-control"
                   value="<?php echo date('Y-m-d\TH:i', strtotime($booking['scheduled_date'])); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="../admin_manage_appointments.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</main>
</body>
</html>
