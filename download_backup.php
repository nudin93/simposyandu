<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if (!isAdmin()) { http_response_code(403); exit; }
$file = basename($_GET['file'] ?? '');
$path = __DIR__ . '/../uploads/backup/' . $file;
if (!$file || !file_exists($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'sql') { http_response_code(404); echo 'File not found'; exit; }
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
