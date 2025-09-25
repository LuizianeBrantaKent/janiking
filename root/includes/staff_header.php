<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$userName  = $_SESSION['name'] ?? 'Staff Test';
$userRole  = $_SESSION['role'] ?? 'Staff';

function staff_user_initials(string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0], 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}
$initials = staff_user_initials($userName);
?>

<header class="header app-header">
  <!-- Left: Brand logo (no sidebar .logo class here) -->
  <div class="brand-header">
    <img src="/assets/images/logo.png" alt="JaniKing Logo" class="logo-img-large">
  </div>

  <!-- Right: User info -->
  <div class="userbox user">
    <div class="user-meta text-end me-2">
      <div class="user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
    </div>
    <div class="avatar avatar-initial"><?= htmlspecialchars($initials) ?></div>
  </div>
</header>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="/assets/css/staff.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    window.APP_BASE = '/staff';
</script>
<script src="/assets/js/staff.js" defer></script>