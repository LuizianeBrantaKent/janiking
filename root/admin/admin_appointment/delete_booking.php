<?php
require_once "../../../db/config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval($_GET["id"]);

    // Handle deletion
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $sql = "DELETE FROM bookings WHERE booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            header("location: ../admin_manage_appointments.php?deleted=1");
            exit;
        } catch (PDOException $e) {
            $error = "Error deleting booking: " . $e->getMessage();
        }
    }

    // Fetch booking for confirmation
    $sql = "SELECT * FROM bookings WHERE booking_id = ?";
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
    <title>Delete Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Delete Booking (#<?php echo $booking['booking_id']; ?>)</h2>
    <p>Are you sure you want to permanently delete this booking scheduled for 
       <strong><?php echo date("M d, Y h:i A", strtotime($booking['scheduled_date'])); ?></strong>?</p>
    <form method="post">
        <button type="submit" class="btn btn-danger">Yes, Delete Booking</button>
        <a href="../admin_manage_appointments.php" class="btn btn-secondary">No, Go Back</a>
    </form>
</body>
</html>
