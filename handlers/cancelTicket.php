<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

header('Content-Type: application/json');

require '../vendor/autoload.php';
require '../system/function.php';

$required_files = [
    __DIR__ . '/../auth/verify_token.php',
    __DIR__ . '/../helpers/getUserCompany.php',
    __DIR__ . '/../helpers/getCompanyInfo.php'
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

// =====================
// 6️⃣ Veritabanı bağlantısı
// =====================
$conn = database();

try {
    // =====================
    // 7️⃣ Bilet bilgilerini al
    // =====================
    $stmt = $conn->prepare("
        SELECT t.user_id, t.trip_id, t.total_price 
        FROM Tickets t 
        WHERE t.ticket_id = ?
    ");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bilet bulunamadı.']);
        exit;
    }
    
    $ticket = $result->fetch_assoc();
    
    // =====================
    // 8️⃣ Kullanıcı kontrolü
    // =====================
    if ($ticket['user_id'] != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Bu bilet size ait değil.']);
        exit;
    }
    
    // =====================
    // 9️⃣ Sefer bilgilerini al
    // =====================
    $stmt = $conn->prepare("
        SELECT arrival_time 
        FROM Trips 
        WHERE trip_id = ?
    ");
    $stmt->bind_param("i", $ticket['trip_id']);
    $stmt->execute();
    $trip_result = $stmt->get_result();
    
    if ($trip_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Sefer bilgisi bulunamadı.']);
        exit;
    }
    
    $trip = $trip_result->fetch_assoc();
    $arrival_time = strtotime($trip['arrival_time']);
    $current_time = time();
    $time_difference = $arrival_time - $current_time;
    
    // =====================
    // 🔟 1 saat kontrolü (3600 saniye)
    // =====================
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
    $conn->begin_transaction();
    
    // Booked_Seats'ten koltukları sil
    $stmt = $conn->prepare("DELETE FROM Booked_Seats WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    
    // Kullanıcının bakiyesine parayı iade et
    $stmt = $conn->prepare("
        UPDATE Users 
        SET balance = balance + ? 
        WHERE user_id = ?
    ");
    $stmt->bind_param("di", $ticket['total_price'], $user_id);
    $stmt->execute();
    
    // Bileti sil
    $stmt = $conn->prepare("DELETE FROM Tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    
    // Transaction'ı commit et
    $conn->commit();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Bilet başarıyla iptal edildi.',
        'refunded_amount' => $ticket['total_price']
    ]);
    
} catch (Exception $e) {
    // Hata durumunda rollback yap
    if ($conn) {
        $conn->rollback();
    }
    error_log("Bilet iptal hatası: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Bilet iptal edilirken bir hata oluştu.'
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>