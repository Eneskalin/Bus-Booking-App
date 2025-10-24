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
    
    // =====================
    // 6️⃣ Bilet bilgilerini al
    // =====================
    $ticket = getTicketInfo($ticket_id);
    
    // HATA AYIKLAMA: Bilet bilgilerini loglayalım
    error_log("Bilet bilgileri: " . json_encode($ticket));
    
    if (!$ticket) {
        echo json_encode(['status' => 'error', 'message' => 'Bilet bulunamadı.']);
        exit;
    }
    
    // =====================
    // 7️⃣ Bilet durumu kontrolü
    // =====================
    if (isset($ticket['status']) && $ticket['status'] === 'cancelled') {
        echo json_encode(['status' => 'error', 'message' => 'Bu bilet zaten iptal edilmiş.']);
        exit;
    }
    
    // =====================
    // 8️⃣ Kullanıcı kontrolü
    // =====================
    if (!isset($ticket['user_id'])) {
        error_log("HATA: Bilet bilgilerinde 'user_id' anahtarı bulunamadı.");
        echo json_encode(['status' => 'error', 'message' => 'Sunucu hatası: Bilet kullanıcı bilgisi eksik.']);
        exit;
    }
    
    if ($ticket['user_id'] != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Bu bilet size ait değil.']);
        exit;
    }
    
    // =====================
    // 9️⃣ Fiyat kontrolü
    // =====================
    if (!isset($ticket['total_price'])) {
        error_log("HATA: Bilet bilgilerinde 'total_price' anahtarı bulunamadı.");
        echo json_encode(['status' => 'error', 'message' => 'Sunucu hatası: Bilet fiyat bilgisi eksik.']);
        exit;
    }
    
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
    if ($departure_timestamp === false) {
        error_log("HATA: Geçersiz tarih formatı: " . $departure_time);
        echo json_encode(['status' => 'error', 'message' => 'Sunucu hatası: Geçersiz tarih formatı.']);
        exit;
    }
    
    $time_difference = $departure_timestamp - time();

    if ($time_difference < 3600) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Seferin kalkmasına 1 saatten az kaldı. İptal işlemi yapılamaz.'
        ]);
        exit;
    }
    
    // =====================
    // 1️⃣1️⃣ Transaction başlat
    // =====================
    $db->beginTransaction();

    // Booked_Seats'ten koltukları sil
    $stmt = $db->prepare("DELETE FROM Booked_Seats WHERE ticket_id = ?");
    if (!$stmt->execute([$ticket_id])) {
        $errorInfo = $stmt->errorInfo();
        error_log("Booked_Seats silme hatası: " . json_encode($errorInfo));
        throw new Exception("Booked_Seats silme hatası: " . $errorInfo[2]);
    }

    // Kullanıcının bakiyesine parayı iade et
    $stmt = $db->prepare("
        UPDATE User
        SET balance = balance + ?
        WHERE id = ?
    ");
    if (!$stmt->execute([$ticket['total_price'], $user_id])) {
        $errorInfo = $stmt->errorInfo();
        error_log("Bakiye iade hatası: " . json_encode($errorInfo));
        throw new Exception("Bakiye iade hatası: " . $errorInfo[2]);
    }

    // Bileti sil
    $stmt = $db->prepare("DELETE FROM Tickets WHERE id = ?");
    if (!$stmt->execute([$ticket_id])) {
        $errorInfo = $stmt->errorInfo();
        error_log("Bilet silme hatası: " . json_encode($errorInfo));
        throw new Exception("Bilet silme hatası: " . $errorInfo[2]);
    }

    $db->commit();

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
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Bilet iptal edilirken bir hata oluştu: ' . $e->getMessage()
    ]);
}
?>