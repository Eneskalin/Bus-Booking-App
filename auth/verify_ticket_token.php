
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function verifyTicket($jwt_token, $secret_key)
{
    if (empty($jwt_token)) {
        return ['valid' => false, 'message' => 'Token bulunamadı.'];
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