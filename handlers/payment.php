<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;


header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';
require '../vendor/autoload.php';
require '../system/function.php';
require '../auth/verify_token.php';
require_once './auth/verfiy_ticket_token.php';



$token_ticket = $_ENV['TICKET_TOKEN'] ?? '';
if (!$token_ticket) {
    echo json_encode(['status' => 'error', 'message' => 'JWT secret tanımlı değil.']);
    exit;
}

$ticket = isset($_GET['ticket']) ? trim($_GET['ticket']) : '';
if (empty($ticket)) {
    echo json_encode(['status' => 'error', 'message' => 'Token gönderilmedi.']);
    exit;
}
$result = verifyJWT($ticket, $ticket_key);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$data = $result['data'];



if (!$secret_key) {
    echo json_encode(['status' => 'error', 'message' => 'JWT secret tanımlı değil.']);
    exit;
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
$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];




?>