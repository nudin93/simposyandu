<?php
require_once __DIR__ . '/config/init.php';
session_destroy();
header('Location: ' . APP_URL . '/index.php');
exit;
