<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
}

$discount_key = $_ENV['DISCOUNT_TOKEN'];

function verifyDiscountToken(string $token): array
{
    global $discount_key;
    
    if (empty($discount_key)) {
        return [
            'valid' => false,
            'message' => 'DISCOUNT_TOKEN yapılandırılmamış.'
        ];
    }
    
    try {
        $decoded = JWT::decode($token, new Key($discount_key, 'HS256'));
        
        // Token'ın geçerli olup olmadığını kontrol et
        if ($decoded->data->status !== 'valid') {
            return [
                'valid' => false,
                'message' => 'Geçersiz kupon token\'ı'
            ];
        }
        
        return [
            'valid' => true,
            'data' => [
                'user_id' => $decoded->data->user_id,
                'coupon_code' => $decoded->data->coupon_code,
                'status' => $decoded->data->status
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'valid' => false,
            'message' => 'Token doğrulanamadı: ' . $e->getMessage()
        ];
    }
}

?>
