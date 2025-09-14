<?php
// Database connection configuration
$host = 'localhost'; // Typically 'localhost' for local servers
$dbname = 'JaniKing';
$username = 'root'; // Replace with your MySQL username (e.g., 'root' for XAMPP)
$password = ''; // Replace with your MySQL password (e.g., empty for default XAMPP)

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exception for debugging
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error or display a generic message in production
    die("Connection failed: " . $e->getMessage());
}
?>