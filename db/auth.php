<?php
declare(strict_types=1);

session_start();

/** Require an authenticated user or redirect to login (stub) */
function require_login(): void {
  if (empty($_SESSION['user'])) {
    header('Location: ' . '/login.php'); // adjust if you have a login page
    exit;
  }
}

/** Get current user array (stub shape) */
function current_user(): array {
  return $_SESSION['user'] ?? [
    'user_id' => 1,
    'name'    => 'Michael Thompson',
    'role'    => 'Staff',
    'avatar'  => asset_url('assets/images/Michael_Thompson.png'),
  ];
}
