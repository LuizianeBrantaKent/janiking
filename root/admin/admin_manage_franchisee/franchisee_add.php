<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../../../db/config.php";
include('../../includes/header.php');
include('../../includes/admin_navbar.php');

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
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO franchisees 
                (business_name,address,abn,start_date,status,point_of_contact,phone,email,password_hash)
                VALUES (:business_name,:address,:abn,:start_date,:status,:point_of_contact,:phone,:email,:password_hash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':business_name',$business_name);
        $stmt->bindParam(':address',$address);
        $stmt->bindParam(':abn',$abn);
        $stmt->bindParam(':start_date',$start_date);
        $stmt->bindParam(':status',$status);
        $stmt->bindParam(':point_of_contact',$point_of_contact);
        $stmt->bindParam(':phone',$phone);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password_hash',$hashed);

        if($stmt->execute()){
            header("Location: ../admin_manage_franchisee.php?added=1");
            exit;
        } else {
            $msg = "Error: Could not add franchisee.";
        }
    } catch(PDOException $e){
        $msg = "Error inserting franchisee: " . $e->getMessage();
    }
}
?>

<div class="main-container">
    <main class="main-content">
        <h1>Add New Franchisee</h1>
        <?php if(!empty($msg)) echo "<div class='alert alert-danger'>$msg</div>"; ?>
        <form method="post" class="form-container">
            <label>Business Name:</label>
            <input type="text" name="business_name" required>

            <label>Address:</label>
            <input type="text" name="address" required>

            <label>ABN:</label>
            <input type="text" name="abn" required>

            <label>Start Date:</label>
            <input type="date" name="start_date" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <label>Point of Contact:</label>
            <input type="text" name="point_of_contact" required>

            <label>Phone:</label>
            <input type="text" name="phone" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-primary">Add Franchisee</button>
        </form>
    </main>
</div>
