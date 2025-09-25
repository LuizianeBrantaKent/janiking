<?php
// includes/db.php
// Not strictly needed for the two pages below, but available for others.

$DB_HOST = '127.0.0.1';
$DB_NAME = 'janiking';
$DB_USER = 'root';
$DB_PASS = '';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  // Fail softly (pages that don't need DB will still render)
  $pdo = null;
}
