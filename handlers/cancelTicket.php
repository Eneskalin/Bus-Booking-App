<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

header('Content-Type: application/json');

require '../vendor/autoload.php';
require_once '../system/function.php';
require_once '../system/config.php'; 
global $db; 

$required_files = [
    __DIR__ . '/../auth/verify_token.php',
    __DIR__ . '/../helpers/getUserCompany.php',
    __DIR__ . '/../helpers/getCompanyInfo.php',
    __DIR__ . '/../helpers/getTicketInfo.php'
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

// =====================
// 5️⃣ POST verilerini al
// =====================
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ticket_id']) || empty($input['ticket_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bilet ID gerekli.']);
    exit;
}

$ticket_id = intval($input['ticket_id']);


try {
    
    $ticket=getTicketInfo($ticket_id);
    
    
    // =====================
    // 8️⃣ Kullanıcı kontrolü
    // =====================
    if ($ticket['user_id'] != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Bu bilet size ait değil.']);
        exit;
    }
    
;
    

    

    
    // =====================
    // 🔟 1 saat kontrolü (3600 saniye)
    // =====================

$departure_time = isset($ticket['trip_datetime']) ? $ticket['trip_datetime'] : null;
if (!$departure_time) {
        error_log("HATA: Bilet bilgileri içinde sefer kalkış zamanı ('trip_datetime' anahtarı) bulunamadı.");
        echo json_encode(['status' => 'error', 'message' => 'Sunucu hatası: Sefer zamanı bilgisi eksik.']);
        exit;
    }
    $departure_timestamp = strtotime($departure_time);
    $time_difference = $departure_timestamp - time();

    if ($time_difference < 3600) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Seferin kalkmasına 1 saatten az kaldı. İptal işlemi yapılamaz.'
        ]);
        exit;
    }
    
// ... try bloğunun içinde

// =====================
// 1️⃣1️⃣ Transaction başlat
// =====================
$db->beginTransaction();

// Booked_Seats'ten koltukları sil (Bu tablo ismini koruyoruz)
$stmt = $db->prepare("DELETE FROM Booked_Seats WHERE ticket_id = ?");
if (!$stmt->execute([$ticket_id])) {
    // Hata durumunda rollback'i tetiklemek için istisna fırlatın
    throw new Exception("Booked_Seats silme hatası.");
}

// Kullanıcının bakiyesine parayı iade et
$stmt = $db->prepare("
    UPDATE Users
    SET balance = balance + ?
    WHERE user_id = ?
");
if (!$stmt->execute([$ticket['total_price'], $user_id])) {
    // Hata durumunda rollback'i tetiklemek için istisna fırlatın
    throw new Exception("Bakiye iade hatası.");
}

// Bileti sil (Sütun adı 'ticket_id' yerine 'id' olarak DÜZELTİLDİ)
$stmt = $db->prepare("DELETE FROM Tickets WHERE id = ?");
if (!$stmt->execute([$ticket_id])) {
    // Hata durumunda rollback'i tetiklemek için istisna fırlatın
    throw new Exception("Bilet silme hatası.");
}


$db->commit();

// ...

echo json_encode([
    'status' => 'success',
    'message' => 'Bilet başarıyla iptal edildi.',
    'refunded_amount' => $ticket['total_price']
]);

} catch (Exception $e) {

    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Bilet iptal hatası: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Bilet iptal edilirken bir hata oluştu.'
    ]);
} finally {
    if (isset($stmt)) {
    }
    if (isset($db)) {
    }
}
?>