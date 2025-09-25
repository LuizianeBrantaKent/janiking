<?php
// staff/staff_training/training_file.php
declare(strict_types=1);

// DEV ONLY (remove when stable)
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

// Simple auth (mirror your app’s rule)
if (!isset($_SESSION['staff_id']) && !isset($_SESSION['user_id'])) {
  header('Location: /login.php'); exit;
}

// NOTE: this file is two levels below project root db/
require_once __DIR__ . '/../../db/config.php';
/** @var PDO $conn */
$pdo = $conn;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo 'Bad request.'; exit; }

// Some MySQL/PDO setups return LOB as string; others as stream
// Also, size_bytes might be NULL if older rows exist; compute a fallback
$stmt = $pdo->prepare("
  SELECT
    filename,
    mime_type,
    COALESCE(size_bytes, OCTET_LENGTH(content)) AS size_bytes,
    content
  FROM training
  WHERE training_id = :id
  LIMIT 1
");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row || $row['content'] === null) {
  http_response_code(404);
  echo 'File not found.';
  exit;
}

$filename = $row['filename'] ?: ('training-'.$id);
$mime     = $row['mime_type'] ?: 'application/octet-stream';
$size     = (int)($row['size_bytes'] ?? 0);
$content  = $row['content'];

// Clean any buffered output before headers (protects against stray BOM/whitespace)
while (ob_get_level() > 0) { ob_end_clean(); }

// Inline types browsers can render; others download
$inlineTypes = [
  'image/jpeg','image/png','image/gif','image/webp','image/svg+xml','image/bmp','image/tiff',
  'application/pdf',
  'audio/mpeg','audio/mp3','audio/wav','audio/ogg','audio/webm','audio/aac','audio/flac',
  'video/mp4','video/webm','video/ogg','video/quicktime',
  'text/plain','text/csv','text/css','text/html','text/xml','application/xml','application/json'
];

$disposition = in_array(strtolower($mime), $inlineTypes, true) ? 'inline' : 'attachment';

// Support HTTP Range (seek/scrub)
$start = 0;
$end   = max(0, $size - 1);
$httpStatus = 200;

if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/i', $_SERVER['HTTP_RANGE'], $m)) {
  if ($m[1] !== '') $start = (int)$m[1];
  if ($m[2] !== '') $end   = (int)$m[2];
  if ($start > $end || $start >= $size) {
    header('HTTP/1.1 416 Range Not Satisfiable');
    header("Content-Range: bytes */{$size}");
    exit;
  }
  $httpStatus = 206;
}

$length = ($size > 0) ? ($end - $start + 1) : 0;

// Safe filename for header
$safeName = preg_replace('/[\r\n]+/',' ', $filename);

if ($httpStatus === 206) header('HTTP/1.1 206 Partial Content');
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($safeName) . '"');
if ($size > 0) {
  header('Content-Length: ' . $length);
  header("Content-Range: bytes {$start}-{$end}/{$size}");
}

// Stream the blob
$chunkSize = 8192;

if (is_resource($content)) {
  // LOB as stream
  // Move to start offset
  if ($start > 0) fseek($content, $start);
  $remaining = $length ?: PHP_INT_MAX; // if size unknown, stream till EOF
  while ($remaining > 0 && !feof($content)) {
    $read = ($length ? min($chunkSize, $remaining) : $chunkSize);
    $buf  = fread($content, $read);
    if ($buf === false) break;
    echo $buf;
    $remaining -= strlen($buf);
    @ob_flush(); @flush();
  }
} else {
  // LOB as string (common)
  if ($size > 0) {
    echo substr($content, $start, $length);
  } else {
    // Size unknown: print whole content
    echo $content;
  }
}
