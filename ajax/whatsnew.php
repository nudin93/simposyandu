<?php
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');
$seen = trim($_GET['seen'] ?? '');
$seen = preg_replace('/[^0-9a-zA-Z.\-]/', '', $seen);
$cur = defined('APP_VERSION') ? APP_VERSION : '1.0.4';
$notes = function_exists('app_changelog_since') ? app_changelog_since($seen) : [];
echo json_encode(['success' => true, 'version' => $cur, 'is_new' => ($seen !== $cur), 'notes' => array_values($notes)], JSON_UNESCAPED_UNICODE);
