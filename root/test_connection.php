<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection configuration
$host = 'localhost';
$dbname = 'janiking';
$username = 'root'; // Your MySQL username
$password = '';     // Your MySQL password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
    $count = $stmt->fetchColumn();
    echo "Connection successful! Found $count bookings in the database.";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>