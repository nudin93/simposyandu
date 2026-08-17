<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json');
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error' => 'ID required']); exit; }
$b = fetchOne("SELECT *, TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) as umur_bulan FROM balita WHERE id=$id");
if ($b) {
    $b['umur'] = hitungUmur($b['tanggal_lahir']);
    echo json_encode(['data' => $b]);
} else {
    echo json_encode(['error' => 'Not found']);
}
