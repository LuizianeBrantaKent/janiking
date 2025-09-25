<?php
// search_contacts.php
require_once "../../../db/config.php";

$term = $_GET['q'] ?? '';
$results = [];

if ($term !== '') {
    $stmt = $conn->prepare("
        SELECT DISTINCT email FROM (
            SELECT email FROM users WHERE email LIKE ?
            UNION ALL
            SELECT email FROM franchisees WHERE email LIKE ?
            UNION ALL
            SELECT email FROM contact_inquiries WHERE email LIKE ?
        ) t
        LIMIT 10
    ");
    $like = '%' . $term . '%';
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

header('Content-Type: application/json');
echo json_encode($results);
