<?php
session_start();
require_once "../../../db/config.php";

// --- ACCESS CONTROL: only admins can access ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied. Only admins can edit franchisees.");
}

// --- GET FRANCHISEE ID ---
$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    die("Invalid franchisee ID.");
}

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Trim inputs
    $business_name = trim($_POST['business_name']);
    $address = trim($_POST['address']);
    $abn = trim($_POST['abn']);
    $start_date = trim($_POST['start_date']);
    $status = trim($_POST['status']);
    $point_of_contact = trim($_POST['point_of_contact']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // --- VALIDATION ---
    $errors = [];

    if (empty($business_name) || empty($address) || empty($abn) || empty($start_date) || empty($status) || empty($point_of_contact) || empty($phone) || empty($email)) {
        $errors[] = "All fields except password are required.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Status validation
    if (!in_array($status, ['Active', 'Inactive'])) {
        $errors[] = "Invalid status selected.";
    }

    // Date validation
    $d = DateTime::createFromFormat('Y-m-d', $start_date);
    if (!$d || $d->format('Y-m-d') !== $start_date) {
        $errors[] = "Invalid start date.";
    }

    // Phone & ABN validation (digits only)
    if (!ctype_digit($phone)) {
        $errors[] = "Phone must contain digits only.";
    }
    if (!ctype_digit($abn)) {
        $errors[] = "ABN must contain digits only.";
    }

    if (empty($errors)) {
        try {
            // --- SQL UPDATE ---
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE franchisees 
                        SET business_name = :business_name, 
                            address = :address, 
                            abn = :abn, 
                            start_date = :start_date, 
                            status = :status, 
                            point_of_contact = :point_of_contact, 
                            phone = :phone, 
                            email = :email, 
                            password_hash = :password_hash 
                        WHERE franchisee_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':password_hash', $hashed);
            } else {
                $sql = "UPDATE franchisees 
                        SET business_name = :business_name, 
                            address = :address, 
                            abn = :abn, 
                            start_date = :start_date, 
                            status = :status, 
                            point_of_contact = :point_of_contact, 
                            phone = :phone, 
                            email = :email
                        WHERE franchisee_id = :id";
                $stmt = $conn->prepare($sql);
            }

            // Bind parameters
            $stmt->bindParam(':business_name', $business_name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':abn', $abn);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':point_of_contact', $point_of_contact);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("Location: ../admin_manage_franchisee.php?updated=1");
                exit;
            } else {
                $errors[] = "Could not update franchisee.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error updating: " . htmlspecialchars($e->getMessage());
        }
    }
}

// --- FETCH EXISTING FRANCHISEE ---
try {
    $sql = "SELECT * FROM franchisees WHERE franchisee_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $franchisee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$franchisee) {
        die("Franchisee not found.");
    }
} catch (PDOException $e) {
    die("Error fetching franchisee: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Franchisee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include('../../includes/header.php'); ?>
<?php include('../../includes/admin_navbar.php'); ?>

<div class="main-container">
    <main class="main-content">
        <h1 class="page-title">Edit Franchisee</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
    <div class="form-group">
        <label>Business Name</label>
        <input type="text" name="business_name" class="form-control" 
               value="<?php echo htmlspecialchars($franchisee['business_name']); ?>" 
               placeholder="Enter business name" required>
        <small class="form-text text-muted">Use the official business name registered with ABN.</small>
    </div>

    <div class="form-group">
        <label>Address</label>
        <input type="text" name="address" class="form-control" 
               value="<?php echo htmlspecialchars($franchisee['address']); ?>" 
               placeholder="Enter full address" required>
        <small class="form-text text-muted">Include street, suburb, state, and postcode.</small>
    </div>

    <div class="form-group">
        <label>ABN</label>
        <input type="text" name="abn" class="form-control" 
               value="<?php echo htmlspecialchars($franchisee['abn']); ?>" 
               placeholder="e.g., 12345678901" required>
        <small class="form-text text-muted">11-digit Australian Business Number (digits only).</small>
    </div>

    <div class="form-group">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control" 
               value="<?php echo htmlspecialchars($franchisee['start_date']); ?>" required>
        <small class="form-text text-muted">Select the franchise start date.</small>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control" required>
            <option value="Active" <?php if ($franchisee['status']=="Active") echo "selected"; ?>>Active</option>
            <option value="Inactive" <?php if ($franchisee['status']=="Inactive") echo "selected"; ?>>Inactive</option>
        </select>
        <small class="form-text text-muted">Set the franchise as Active or Inactive.</small>
    </div>

    <div class="form-group">
        <label>Point of Contact</label>
        <input type="text" name="point_of_contact" class="form-control" 
               value="<?php echo htmlspecialchars($franchisee['point_of_contact']); ?>" 
               placeholder="Enter contact person name" required>
        <small class="form-text text-muted">Main contact person for this franchise.</small>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" 
                   value="<?php echo htmlspecialchars($franchisee['phone']); ?>" 
                   placeholder="e.g., 0412345678" required>
            <small class="form-text text-muted">Include area code if required. Digits only.</small>
        </div>
        <div class="form-group col-md-6">
            <label>Email</label>
            <input type="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($franchisee['email']); ?>" 
                   placeholder="e.g., contact@example.com" required>
            <small class="form-text text-muted">Use a valid email address for official communication.</small>
        </div>
    </div>

    <div class="form-group">
        <label>Password <small>(leave blank to keep unchanged)</small></label>
        <input type="password" name="password" class="form-control" 
               placeholder="Enter new password if changing">
        <small class="form-text text-muted">Minimum 8 characters recommended. Leave blank to keep existing password.</small>
    </div>

    <button type="submit" class="btn btn-success">Update Franchisee</button>
    <a href="../admin_manage_franchisee.php" class="btn btn-secondary">Cancel</a>
</form>

    </main>
</div>
</body>
</html>
