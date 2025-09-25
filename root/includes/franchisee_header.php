<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Pull directly from session, matching your SQL column names
$userName  = $_SESSION['name'] ?? 'Test test';
$userRole  = $_SESSION['role'] ?? 'test'; // 'Admin' | 'Staff' | 'Franchisee'
$brandLogoSrc = '/../assets/images/logo.png';

// Helper: initials from name
function user_initials(string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0], 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}
$initials = user_initials($userName);

// Format role for display
$roleLabel = htmlspecialchars(ucfirst(strtolower($userRole)));
?>

<header class="app-header">
  <div class="header-left">
    <div class="brand">
      <img src="../assets/images/logo.png" alt="JaniKing Logo" class="logo-img">
    </div>
 </div>

  <div class="userbox">
    <div class="user-meta">
      <div class="user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
    </div>
    <div class="avatar-initial"><?= $initials ?></div>
  </div>
</header>
