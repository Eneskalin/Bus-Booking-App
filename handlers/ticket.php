<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;


header('Content-Type: application/json');

require '../vendor/autoload.php';
require '../system/function.php';



$secret_key = $_ENV['JWT_SECRET_KEY'] ?? '';
$token_ticket = $_ENV['TICKET_TOKEN'] ?? '';


if (!$secret_key) {
    echo json_encode(['status' => 'error', 'message' => 'JWT secret tanımlı değil.']);
    exit;
}

// =====================
// 2️⃣ JWT doğrulama fonksiyonu
// =====================
function verifyJWT($jwt_token, $secret_key)
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
                'role' => $decoded->data->role
            ]
        ];
    } catch (\Exception $e) {
        return ['valid' => false, 'message' => 'Token doğrulanamadı: ' . $e->getMessage()];
    }
}

// =====================
// 3️⃣ Header'dan token al
// =====================
$token = null;
$headers = function_exists('getallheaders') ? getallheaders() : [];

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($headers['authorization'])) {
    $token = str_replace('Bearer ', '', $headers['authorization']);
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
}

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token bulunamadı. Lütfen giriş yapın.']);
    exit;
}

// =====================
// 4️⃣ Token doğrula
// =====================
$result = verifyJWT($token, $secret_key);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];

// =====================
// 5️⃣ Gönderilen JSON verisini al
// =====================
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['tripId'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
    exit;
}

$tripId = $data['tripId'];
$totalPrice = $data['totalPrice'] ?? 0;
$passengers = $data['passengers'] ?? [];

// =====================
// 6️⃣ Ticket Token oluştur
// =====================
$timestamp = time();
$expiration_time = $timestamp + (60 * 60 * 3);
$payload = [
    'iat' => $timestamp,
    'nbf' => $timestamp,
    'exp' => $expiration_time,

    'data' => [
        'user_id' => $user_id,
        'username' => $username,
        'trip_id' => $tripId,
        'total_price' => $totalPrice, 
        'passengers' => $passengers
    ]
];
$ticket_token = JWT::encode($payload, $token_ticket, 'HS256');


echo json_encode([
    'success' => true,
    'message' => 'Bilet oluşturuldu',
    'token' => $ticket_token,
    'redirect_url' => '/seats.php?ticket=' . urlencode($ticket_token)
]);
exit;