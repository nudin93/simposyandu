<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
$file = basename($_GET['file'] ?? '');
$path = __DIR__ . '/../uploads/backup/' . $file;
if (!$file || !file_exists($path)) jsonResponse(false, 'File tidak ditemukan');
if (unlink($path)) jsonResponse(true, 'File backup berhasil dihapus');
else jsonResponse(false, 'Gagal menghapus file');
