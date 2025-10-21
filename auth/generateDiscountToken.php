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
    // Hata durumunda uygun bir işlem yapabilirsiniz
}

$payment_key = $_ENV['DISCOUNT_TOKEN'];


function generateDiscountToken(int $user_id, string $coupon_code, string $status): string
{
    global $payment_key;
    
    $timestamp = time();
    $expiration_time = $timestamp + (60 * 60 );

    $payload = [
        'iat' => $timestamp,
        'nbf' => $timestamp,
        'exp' => $expiration_time,

        'data' => [
            'user_id' => $user_id,
            'coupon_code' => $coupon_code,
            'status' => $status, 
        ]
    ];

    $jwt = JWT::encode($payload, $payment_key, 'HS256');
    return $jwt;
}