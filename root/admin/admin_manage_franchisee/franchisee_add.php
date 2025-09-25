<?php
require_once "../../../db/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $business_name   = trim($_POST['business_name']);
    $address         = trim($_POST['address']);
    $abn             = preg_replace('/\D/', '', $_POST['abn']); // keep only digits
    $start_date      = trim($_POST['start_date']);
    $status          = trim($_POST['status']);
    $point_of_contact= trim($_POST['point_of_contact']);
    $phone           = preg_replace('/[^0-9+]/', '', $_POST['phone']); // allow numbers + "+"
    $email           = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password        = trim($_POST['password']);

    // --- Server-side validations ---
    if (!$email) {
        $error = "Invalid email format.";
    } elseif (strlen($abn) != 11) {
        $error = "ABN must be exactly 11 digits.";
    } elseif (!preg_match('/^(\+61|0)[0-9]{9}$/', $phone)) {
        $error = "Invalid Australian phone number format.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters, include uppercase, lowercase, a number, and a special character.";
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO franchisees 
                    (business_name, address, abn, start_date, status, point_of_contact, phone, email, password_hash) 
                    VALUES 
                    (:business_name, :address, :abn, :start_date, :status, :point_of_contact, :phone, :email, :password_hash)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':business_name', $business_name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':abn', $abn);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':point_of_contact', $point_of_contact);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $hashed);

            if ($stmt->execute()) {
                header("Location: ../admin_manage_franchisee.php?added=1");
                exit;
            } else {
                $error = "Could not add franchisee.";
            }
        } catch (PDOException $e) {
            $error = "Error inserting franchisee: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Franchisee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
        <main class="main-content">
            <h1 class="page-title">Add New Franchisee</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" name="business_name" class="form-control" placeholder="e.g. Sparkle Clean Services Pty Ltd" required>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. 123 George St, Sydney NSW 2000" required>
                </div>

                <div class="form-group">
                    <label>ABN</label>
                    <input type="text" name="abn" class="form-control" placeholder="11-digit ABN (no spaces)" pattern="\d{11}" required>
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Point of Contact</label>
                    <input type="text" name="point_of_contact" class="form-control" placeholder="e.g. John Smith" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+61 400 000 000" pattern="^(\+61|0)[0-9]{9}$" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. name@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" 
                           class="form-control" 
                           placeholder="Min. 8 chars, include uppercase, lowercase, number & symbol"
                           pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$" 
                           title="Password must be at least 8 characters, include uppercase, lowercase, a number, and a special character." 
                           required>
                </div>

                <button type="submit" class="btn btn-success">Add Franchisee</button>
                <a href="../admin_manage_franchisee.php" class="btn btn-secondary">Cancel</a>
            </form>
        </main>
    </div>
</body>
</html>
