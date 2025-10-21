<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

require '../vendor/autoload.php';
require '../system/config.php'; 

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
    send_json_response("error", "Sunucu konfigürasyon hatası.");
}

$secret_key = $_ENV['JWT_SECRET_KEY'];

function generateJWT(int $user_id, string $full_name, string $role): string
{
    global $secret_key;
    
    $timestamp = time();
    $expiration_time = $timestamp + (60 * 60 * 48); 

    $payload = [
        'iat' => $timestamp,
        'nbf' => $timestamp,
        'exp' => $expiration_time,
        'data' => [
            'user_id' => $user_id,
            'username' => $full_name,
            'role' => $role
        ]
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');
    return $jwt;
}