<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
if (empty($_FILES['sql_file']['name'])) jsonResponse(false, 'File SQL tidak dipilih');
$tmp = $_FILES['sql_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION));
if ($ext !== 'sql') jsonResponse(false, 'Hanya file .sql yang diizinkan');
$sql_content = file_get_contents($tmp);
if (!$sql_content) jsonResponse(false, 'File SQL kosong atau tidak dapat dibaca');
global $conn;
$conn->multi_query($sql_content);
do { if ($res = $conn->store_result()) $res->free(); } while ($conn->more_results() && $conn->next_result());
if ($conn->errno) jsonResponse(false, 'Restore gagal: ' . $conn->error);
jsonResponse(true, 'Database berhasil di-restore!');
