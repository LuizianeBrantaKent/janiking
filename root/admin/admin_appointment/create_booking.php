<?php
require_once "../../../db/config.php";

// Check connection
if (!isset($conn)) {
    die("Database connection failed. Please check your configuration.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $franchisee_id      = $_POST["franchisee_id"];
    $first_name         = $_POST["first_name"];
    $last_name          = $_POST["last_name"];
    $email              = $_POST["email"];
    $phone              = $_POST["phone"];
    $preferred_location = $_POST["preferred_location"];
    $scheduled_date     = $_POST["scheduled_date"];
    $status             = $_POST["status"];
    $notes              = $_POST["notes"];

    try {
        $sql = "INSERT INTO bookings 
                (franchisee_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $franchisee_id, $first_name, $last_name, $email, $phone,
            $preferred_location, $scheduled_date, $status, $notes
        ]);
        
        header("location: ../admin_manage_appointments.php?success=1");
        exit;
    } catch (PDOException $e) {
        $error = "Error: Could not create booking. " . $e->getMessage();
    }
}

// Fetch franchisees for dropdown
$franchisees = [];
try {
    $sql = "SELECT franchisee_id, business_name FROM franchisees ORDER BY business_name ASC";
    $stmt = $conn->query($sql);
    $franchisees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching franchisees: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container-form {
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004990;
            margin-bottom: 20px;
        }
        .btn-success {
            background-color: #004990;
            border-color: #004990;
        }
        .btn-success:hover {
            background-color: #003b73;
            border-color: #003b73;
        }
    </style>
</head>
<body>
    <div class="container-form">
        <h2>Create New Booking</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Franchisee</label>
                <select name="franchisee_id" class="form-control" required>
                    <option value="">-- Select Franchisee --</option>
                    <?php foreach($franchisees as $f): ?>
                        <option value="<?php echo $f['franchisee_id']; ?>">
                            <?php echo htmlspecialchars($f['business_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Preferred Location</label>
                <input type="text" name="preferred_location" class="form-control">
            </div>
            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="scheduled_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Create Booking</button>
            <a href="../admin_manage_appointments.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>