<?php
// includes/boot.php
// Single include to normalize paths, expose helpers and (optionally) DB.

$APP_ROOT = realpath(__DIR__ . '/..');
define('APP_ROOT', $APP_ROOT);

// Compute BASE_URL from filesystem path → URL path (works under XAMPP)
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$appRoot = rtrim(str_replace('\\', '/', $APP_ROOT), '/');
$base    = str_replace($docRoot, '', $appRoot);
if ($base === '' || $base[0] !== '/') $base = '/' . ltrim($base, '/');
define('BASE_URL', rtrim($base, '/'));

// Helpers + (optional) DB
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

// Defaults for the header UI
$logoPath   = asset_url('assets/images/logo1.png');
$avatarPath = asset_url('assets/images/Michael_Thompson.png');
$userName   = 'Michael Thompson';
$userRole   = 'Staff';
