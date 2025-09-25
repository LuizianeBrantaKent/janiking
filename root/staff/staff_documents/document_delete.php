<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once "../../../db/config.php"; // $conn must be a PDO

// Helper: simple escaper (optional)
if (!function_exists('e')) {
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// Where uploaded files live on disk (adjust if yours differs)
$DOC_ROOT = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$PROJECT_ROOT = realpath($DOC_ROOT); // fallback guard
$UPLOAD_BASE = realpath($DOC_ROOT . '/uploads'); // e.g. /var/www/html/uploads
if ($UPLOAD_BASE === false) {
    // If your uploads are somewhere else, change this to that directory.
    $UPLOAD_BASE = $PROJECT_ROOT;
}

function resolve_absolute_path(string $storedPath, string $DOC_ROOT, string $PROJECT_ROOT): ?string {
    // If it looks like a URL, we can't unlink it.
    if (preg_match('#^https?://#i', $storedPath)) return null;

    // If it starts with / treat it as web-root relative
    if (strpos($storedPath, '/') === 0) {
        $candidate = $DOC_ROOT . $storedPath;
    } else {
        // Treat as project-root relative from this file location
        $candidate = __DIR__ . '/../../../' . $storedPath;
    }

    $real = realpath($candidate);
    return $real !== false ? str_replace('\\','/',$real) : null;
}

// Require an id
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Missing document id.';
    header("Location: ../staff_documents.php");
    exit;
}

$id = (int) $_GET['id'];

try {
    // Fetch file path
    // NOTE: verify your column names match exactly. If your PK is 'document_id', change both queries.
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE documents_id = ? LIMIT 1");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        $_SESSION['error'] = 'Document not found.';
        header("Location: ../staff_documents.php");
        exit;
    }

    $storedPath = (string)($file['file_path'] ?? '');
    $absPath = resolve_absolute_path($storedPath, $DOC_ROOT, $PROJECT_ROOT);

    // Security: only delete if inside uploads (or project root as a last resort)
    $okToDelete = false;
    if ($absPath) {
        $inUploads = (strpos($absPath, rtrim($UPLOAD_BASE,'/').'/') === 0) || ($absPath === $UPLOAD_BASE);
        $inProject = (strpos($absPath, rtrim($PROJECT_ROOT,'/').'/') === 0) || ($absPath === $PROJECT_ROOT);
        // Prefer uploads; fall back to project root if that’s how you store files
        $okToDelete = $inUploads || $inProject;
    }

    if ($absPath && $okToDelete && is_file($absPath)) {
        // suppress warning but detect failure
        if (!@unlink($absPath)) {
            // Not fatal—still remove DB row, but tell the user
            $_SESSION['error'] = 'File record removed, but physical file could not be deleted (permissions).';
        }
    } else {
        // If it was a URL or missing on disk, just proceed with DB delete
        // (optional) you can set a soft warning:
        // $_SESSION['error'] = 'File not found on disk. Record removed.';
    }

    // Delete DB record
    $del = $conn->prepare("DELETE FROM documents WHERE documents_id = ? LIMIT 1");
    $del->execute([$id]);

    if (!isset($_SESSION['error'])) {
        $_SESSION['success'] = 'Document deleted successfully.';
    } else {
        // If there was a file deletion warning, still mark as partial success
        $_SESSION['success'] = ($_SESSION['success'] ?? 'Record removed.');
    }

} catch (Throwable $e) {
    $_SESSION['error'] = 'Error deleting document: ' . $e->getMessage();
}

header("Location: ../staff_documents.php");
exit;
