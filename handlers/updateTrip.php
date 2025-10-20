<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

header('Content-Type: application/json');

require '../vendor/autoload.php';
require '../system/function.php';

$required_files = [
    __DIR__ . '/../auth/verify_token.php',
    __DIR__ . '/../helpers/getUserCompany.php'
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
// Token al ve doğrula
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

$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$role = $result['data']['role'];

if($role != "company"){
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// =====================
// POST verilerini al
// =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Sadece POST metodu kabul edilir']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz JSON verisi']);
    exit;
}

$trip_id = isset($input['trip_id']) ? (int)$input['trip_id'] : 0;
$departure_city = isset($input['departure_city']) ? trim($input['departure_city']) : '';
$destination_city = isset($input['destination_city']) ? trim($input['destination_city']) : '';
$price = isset($input['price']) ? (float)$input['price'] : 0;
$departure_date = isset($input['departure_date']) ? trim($input['departure_date']) : '';
$departure_time = isset($input['departure_time']) ? trim($input['departure_time']) : '';
$arrival_time = isset($input['arrival_time']) ? trim($input['arrival_time']) : '';
$capacity = isset($input['capacity']) ? (int)$input['capacity'] : 0;

// =====================
// Validasyon
// =====================
if ($trip_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sefer ID']);
    exit;
}

if (empty($departure_city) || empty($destination_city)) {
    echo json_encode(['success' => false, 'message' => 'Kalkış ve varış şehri gereklidir']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçerli bir fiyat giriniz']);
    exit;
}

if (empty($departure_date) || empty($departure_time) || empty($arrival_time)) {
    echo json_encode(['success' => false, 'message' => 'Tarih ve saat bilgileri gereklidir']);
    exit;
}

if ($capacity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçerli bir kapasite giriniz']);
    exit;
}

// =====================
// Şirket kontrolü
// =====================
$company_id = getUserCompany($user_id);
if (!$company_id) {
    echo json_encode(['success' => false, 'message' => 'Şirket bilgisi bulunamadı']);
    exit;
}

try {
    // Seferin bu şirkete ait olup olmadığını kontrol et
    $check_sql = 'SELECT id FROM Trips WHERE id = :trip_id AND company_id = :company_id';
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->execute([':trip_id' => $trip_id, ':company_id' => $company_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bu seferi düzenleme yetkiniz yok']);
        exit;
    }

    // Tarih ve saati birleştir
    $departure_datetime = $departure_date . ' ' . $departure_time;
    $arrival_datetime = $departure_date . ' ' . $arrival_time;

    // Seferi güncelle
    $update_sql = '
        UPDATE Trips 
        SET 
            departure_city = :departure_city,
            destination_city = :destination_city,
            departure_time = :departure_time,
            arrival_time = :arrival_time,
            price = :price,
            capacity = :capacity
        WHERE id = :trip_id AND company_id = :company_id
    ';

    $update_stmt = $db->prepare($update_sql);
    $result = $update_stmt->execute([
        ':departure_city' => $departure_city,
        ':destination_city' => $destination_city,
        ':departure_time' => $departure_datetime,
        ':arrival_time' => $arrival_datetime,
        ':price' => $price,
        ':capacity' => $capacity,
        ':trip_id' => $trip_id,
        ':company_id' => $company_id
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Sefer başarıyla güncellendi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sefer güncellenirken hata oluştu']);
    }

} catch (PDOException $e) {
    error_log('updateTrip DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası oluştu']);
}

exit;
