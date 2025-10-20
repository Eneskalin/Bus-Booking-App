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
    __DIR__ . '/../helpers/getseats.php'
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

$ticket_key = $_ENV['TICKET_TOKEN'] ?? '';
$secret_key = $_ENV['JWT_SECRET_KEY'] ?? '';

// =====================
// TICKET TOKEN DOĞRULAMA 
// =====================

if (empty($ticket_key)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'TICKET_TOKEN yapılandırılmamış.'
    ]);
    exit;
}

$ticket = isset($_GET['ticket']) ? trim($_GET['ticket']) : '';
if (empty($ticket)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Ticket Token gönderilmedi.'
    ]);
    exit;
}

$result = verifyTicket($ticket, $ticket_key);
if (!$result['valid']) {
    echo json_encode([
        'status' => 'error', 
        'message' => $result['message']
    ]);
    exit;
}

$data = $result['data'];

// =====================
// BÜTÇE KONTROLÜ
// =====================

$budget = getBalance($data['user_id']);

if ($data['total_price'] > $budget) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Kredi yeterli değil.'
    ]);
    exit;
}

// =====================
// KULLANICI TOKEN DOĞRULAMA 
// =====================

if (empty($secret_key)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'JWT_SECRET_KEY yapılandırılmamış.'
    ]);
    exit;
}

// Authorization Header'dan Token Al
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

if (empty($token)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Token bulunamadı. Lütfen giriş yapın.'
    ]);
    exit;
}

$result = verifyJWT($token); 
if (!$result['valid']) {
    echo json_encode([
        'status' => 'error', 
        'message' => $result['message']
    ]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];

// =====================
// GÖNDERİLEN KOLTUKLARI ALMA
// =====================

$input = file_get_contents('php://input');
$request_data = json_decode($input, true);

$selected_seats = $request_data['selected_seats'] ?? [];

if (!is_array($selected_seats) || empty($selected_seats)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçerli koltuk bilgisi bulunamadı.'
    ]);
    exit;
}

if (count($selected_seats) != $data['passengers']) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Koltuk sayısı (' . count($selected_seats) . ') ile yolcu sayısı (' . $data['passengers'] . ') eşleşmiyor.'
    ]);
    exit;
}


$booked_seats = getSeats($data['trip_id']);
$intersection = array_intersect($selected_seats, $booked_seats);
if (!empty($intersection)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Seçtiğiniz koltuklardan bazıları (' . implode(', ', $intersection) . ') önceden alınmıştır. Lütfen sayfayı yenileyin.',
        'booked' => $booked_seats 
    ]);
    exit;
}

// =====================
// VERİTABANINA KAYDET
// =====================

try {
    setBalance($data['user_id'],$data['total_price']);
    setTicketInfo($data['trip_id'], $data['user_id'], $data['total_price']);
    
    $success_count = 0;
    $error_seats = [];

    foreach ($selected_seats as $seat) {
        $setSeat_result = setSeat($data['trip_id'], $seat);
        
        if ($setSeat_result) { 
            $success_count++;
        } else {
            $error_seats[] = $seat;
            error_log("Koltuk No: " . $seat . " kaydedilemedi. Yolculuk ID: " . $data['trip_id']);
        }
    }
    
    if (empty($error_seats)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Ödeme başarılı ve ' . $success_count . ' bilet kaydedildi.',
            'user' => $result['data']
        ]);
    } else {

        echo json_encode([
            'status' => 'error',
            'message' => 'Ödeme yapıldı ancak bazı koltuklar kaydedilemedi.',
            'success_count' => $success_count,
            'error_seats' => $error_seats
        ]);
    }
    
} catch (\Exception $e) {
    error_log("Bilet/Koltuk kaydetme hatası: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Bilet kaydedilemedi: ' . $e->getMessage()
    ]);
}

?>

