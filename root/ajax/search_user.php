<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();
require_once "../db/config.php";  // Correct: From htdocs/ajax/ up to parent/db/

if (!isset($_SESSION['user_id'])) {
    error_log("search_user.php: No session - returning []");
    echo json_encode([]);
    exit;
}

$input = $_GET['email'] ?? '';
if (empty($input)) {
    error_log("search_user.php: Empty input - returning []");
    echo json_encode([]);
    exit;
}

$email = $input . '%';  // Prefix match: "a" → "a%"
error_log("search_user.php: Searching for '$input' (LIKE '$email') | user_id: " . ($_SESSION['user_id'] ?? 'none'));

try {
    $stmt = $conn->prepare("
        SELECT user_id, email, name FROM users WHERE email LIKE ? 
        UNION 
        SELECT franchisee_id AS user_id, email, point_of_contact AS name FROM franchisees WHERE email LIKE ?
    ");
    $stmt->execute([$email, $email]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("search_user.php: Found " . count($users) . " results: " . json_encode($users));
} catch (Exception $e) {
    error_log("search_user.php DB Error: " . $e->getMessage());
    echo json_encode([]);
    exit;
}

echo json_encode($users);
?>