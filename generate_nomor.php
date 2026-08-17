<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json');
$type = $_GET['type'] ?? '';
$map = ['balita' => ['BLT', 'balita'], 'ibu_hamil' => ['IBH', 'ibu_hamil'], 'lansia' => ['LNS', 'lansia']];
if (!isset($map[$type])) { echo json_encode(['error' => 'Invalid type']); exit; }
[$prefix, $table] = $map[$type];
echo json_encode(['nomor' => generateNomorPeserta($prefix, $table)]);
