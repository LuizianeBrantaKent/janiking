<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../../../db/config.php";
include('../../includes/header.php');        // your global header
include('../../includes/admin_navbar.php'); // navbar

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid franchisee ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $business_name = trim($_POST['business_name']);
    $address = trim($_POST['address']);
    $abn = trim($_POST['abn']);
    $start_date = trim($_POST['start_date']);
    $status = trim($_POST['status']);
    $point_of_contact = trim($_POST['point_of_contact']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE franchisees 
                    SET business_name=:business_name, address=:address, abn=:abn, start_date=:start_date,
                        status=:status, point_of_contact=:point_of_contact, phone=:phone, email=:email,
                        password_hash=:password_hash
                    WHERE franchisee_id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password_hash', $hashed);
        } else {
            $sql = "UPDATE franchisees 
                    SET business_name=:business_name, address=:address, abn=:abn, start_date=:start_date,
                        status=:status, point_of_contact=:point_of_contact, phone=:phone, email=:email
                    WHERE franchisee_id=:id";
            $stmt = $conn->prepare($sql);
        }

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
            $msg = "Error: Could not update franchisee.";
        }
    } catch (PDOException $e) {
        $msg = "Error updating: " . $e->getMessage();
    }
}

// Fetch franchisee
try {
    $sql = "SELECT * FROM franchisees WHERE franchisee_id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $franchisee = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$franchisee) die("Franchisee not found.");
} catch (PDOException $e) {
    die("Error fetching franchisee: " . $e->getMessage());
}
?>

<div class="main-container">
    <main class="main-content">
        <h1>Edit Franchisee</h1>
        <?php if(!empty($msg)) echo "<div class='alert alert-danger'>$msg</div>"; ?>
        <form method="post" class="form-container">
            <label>Business Name:</label>
            <input type="text" name="business_name" value="<?= htmlspecialchars($franchisee['business_name']) ?>" required>

            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($franchisee['address']) ?>" required>

            <label>ABN:</label>
            <input type="text" name="abn" value="<?= htmlspecialchars($franchisee['abn']) ?>" required>

            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($franchisee['start_date']) ?>" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="Active" <?= $franchisee['status']=="Active"?"selected":"" ?>>Active</option>
                <option value="Inactive" <?= $franchisee['status']=="Inactive"?"selected":"" ?>>Inactive</option>
            </select>

            <label>Point of Contact:</label>
            <input type="text" name="point_of_contact" value="<?= htmlspecialchars($franchisee['point_of_contact']) ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($franchisee['phone']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($franchisee['email']) ?>" required>

            <label>Password (leave blank to keep unchanged):</label>
            <input type="password" name="password">

            <button type="submit" class="btn btn-primary">Update Franchisee</button>
        </form>
    </main>
</div>
