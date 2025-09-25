<?php
// staff/staff_documents/documents_file.php — streams a file from the `uploads` table
declare(strict_types=1);

// DEV ONLY (uncomment while debugging)
// ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['staff_id']) && !isset($_SESSION['user_id'])) {
  header('Location: /login.php'); exit;
}

// NOTE: this file is two levels below /db/
require_once __DIR__ . '/../../db/config.php';
/** @var PDO $conn */
$pdo = $conn;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo 'Bad request.'; exit; }

$download = isset($_GET['download']); // ?download=1 forces attachment

$stmt = $pdo->prepare("
  SELECT
    filename,
    mime_type,
    COALESCE(size_bytes, OCTET_LENGTH(content)) AS size_bytes,
    content
  FROM uploads
  WHERE id = :id
  LIMIT 1
");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row || $row['content'] === null) {
  http_response_code(404);
  echo 'File not found.';
  exit;
}

$filename = $row['filename'] ?: ('file-'.$id);
$mime     = $row['mime_type'] ?: 'application/octet-stream';
$size     = (int)($row['size_bytes'] ?? 0);
$content  = $row['content'];

// Clear any buffered output before headers
while (ob_get_level() > 0) { ob_end_clean(); }

// Which types can be displayed inline? (others will download)
$inlineTypes = [
  'image/jpeg','image/png','image/gif','image/webp','image/svg+xml','image/bmp','image/tiff',
  'application/pdf',
  'audio/mpeg','audio/mp3','audio/wav','audio/ogg','audio/webm','audio/aac','audio/flac',
  'video/mp4','video/webm','video/ogg','video/quicktime',
  'text/plain','text/csv','text/css','text/html','text/xml','application/xml','application/json'
];
$disposition = ($download || !in_array(strtolower($mime), $inlineTypes, true)) ? 'attachment' : 'inline';

// HTTP Range support (media scrubbing)
$start = 0;
$end   = max(0, $size - 1);
$status = 200;

if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/i', $_SERVER['HTTP_RANGE'], $m)) {
  if ($m[1] !== '') $start = (int)$m[1];
  if ($m[2] !== '') $end   = (int)$m[2];
  if ($start > $end || ($size > 0 && $start >= $size)) {
    header('HTTP/1.1 416 Range Not Satisfiable');
    header("Content-Range: bytes */{$size}");
    exit;
  }
  $status = 206;
}

$length   = ($size > 0) ? ($end - $start + 1) : 0;
$safeName = preg_replace('/[\r\n]+/',' ', $filename);

if ($status === 206) header('HTTP/1.1 206 Partial Content');
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($safeName) . '"');
if ($size > 0) {
  header('Content-Length: ' . $length);
  header("Content-Range: bytes {$start}-{$end}/{$size}");
}

// Stream the blob (works for LOB string or stream)
$chunk = 8192;
if (is_resource($content)) {
  if ($start > 0) fseek($content, $start);
  $remain = $length ?: PHP_INT_MAX;
  while ($remain > 0 && !feof($content)) {
    $read = $length ? min($chunk, $remain) : $chunk;
    $buf = fread($content, $read);
    if ($buf === false) break;
    echo $buf;
    $remain -= strlen($buf);
    @ob_flush(); @flush();
  }
} else {
  echo ($size > 0) ? substr($content, $start, $length) : $content;
}
