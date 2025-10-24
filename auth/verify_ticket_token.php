
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
}

function verifyTicket($jwt_token)
{
    if (empty($jwt_token)) {
        return ['valid' => false, 'message' => 'Token bulunamadı.'];
    }

    // Get secret key from environment variables
    $secret_key = $_ENV['TICKET_TOKEN'] ?? '';
    if (!$secret_key) {
        return ['valid' => false, 'message' => 'JWT secret tanımlı değil.'];
    }

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
                'trip_id' => $decoded->data->trip_id,
                'total_price' => $decoded->data->total_price,
                'passengers' => $decoded->data->passengers

            ]
        ];
    } catch (\Exception $e) {
        return ['valid' => false, 'message' => 'Token doğrulanamadı: ' . $e->getMessage()];
    }
}
?>