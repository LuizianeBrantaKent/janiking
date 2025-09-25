<?php
// includes/helpers.php

// Safe escape for HTML (accepts nulls without TypeError)
if (!function_exists('e')) {
  function e($s = '') {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

// Build a public URL to any asset/path relative to app root
if (!function_exists('asset_url')) {
  function asset_url($path = '') {
    $base = defined('BASE_URL') ? BASE_URL : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
  }
}

// Build a filesystem path from app root
if (!function_exists('asset_path')) {
  function asset_path($path = '') {
    $root = defined('APP_ROOT') ? APP_ROOT : realpath(__DIR__ . '/..');
    return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/'));
  }
}
