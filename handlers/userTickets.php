<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

header('Content-Type: application/json');

$autoload_path = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoload_path)) {
    error_log("HATA: vendor/autoload.php bulunamadı: " . $autoload_path);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Sunucu yapılandırma hatası.'
    ]);
    exit;
}

require_once $autoload_path;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (\Exception $e) {
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Sunucu yapılandırma hatası (.env).'
    ]);
    exit;
}

$required_files = [
    __DIR__ . '/../auth/verify_token.php',
    __DIR__ . '/../auth/verify_ticket_token.php',
    __DIR__ . '/../helpers/getBalance.php',
    __DIR__ . '/../helpers/setTicket.php',
    __DIR__ . '/../helpers/setBalance.php',
    __DIR__ . '/../helpers/setBookedSeat.php',
    __DIR__ . '/../helpers/getseats.php',
    __DIR__ . '/../helpers/getUserTickets.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        error_log("HATA: Gerekli dosya bulunamadı: " . $file);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Sunucu yapılandırma hatası (eksik dosya).'
        ]);
        exit;
    }
    require_once $file;
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

$tickets=getTickets($user_id);


echo json_encode([
    'success' => true,
    'tickets' => $tickets,
]);


?>