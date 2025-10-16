<?php
require '../vendor/autoload.php';
require '../system/function.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

header('Content-Type: application/json');

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Sunucu yapılandırma hatası.']);
    exit;
}

$secret_key = $_ENV['JWT_SECRET_KEY'];


function verifyJWT($jwt_token)
{
    global $secret_key;

    try {
        $decoded = JWT::decode($jwt_token, new Key($secret_key, 'HS256'));

        if ($decoded->exp < time()) {
            return ['valid' => false, 'message' => 'Token süresi dolmuş.'];
        }

        return [
            'valid' => true,
            'data' => [
                'user_id' => $decoded->data->user_id,
                'username' => $decoded->data->username,
                'role' => $decoded->data->role
            ]
        ];

    } catch (\Firebase\JWT\ExpiredException $e) {
        return ['valid' => false, 'message' => 'Token süresi dolmuş.'];
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        return ['valid' => false, 'message' => 'Token imzası geçersiz.'];
    } catch (\Exception $e) {
        return ['valid' => false, 'message' => 'Token doğrulanamadı: ' . $e->getMessage()];
    }
}

$headers = getallheaders();
$jwt_token = null;

if (isset($headers['Authorization'])) {
    $jwt_token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($_POST['token'])) {
    $jwt_token = $_POST['token'];
}

if (!$jwt_token) {
    echo json_encode(['status' => 'error', 'message' => 'Token bulunamadı.']);
    exit;
}

$result = verifyJWT($jwt_token);

if ($result['valid']) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Token geçerli.',
        'user' => $result['data']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);
}
