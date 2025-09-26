<?php
date_default_timezone_set('Australia/Sydney'); // Ensure timezone consistency
header('Cache-Control: no-cache, no-store, must-revalidate'); // Prevent caching
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

session_start();

// Clear all session variables
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy session if active
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
} else {
    error_log("Logout attempted with no active session at " . date('Y-m-d H:i:s'));
}

header('Location: /login.php?logout=success');
exit;
?>