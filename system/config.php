<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Sunucu yapılandırma hatası.']);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


date_default_timezone_set('Europe/Istanbul');

try {
    $db_path = __DIR__ . '/../db/bus.sqlite';
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    $db = null;
}
