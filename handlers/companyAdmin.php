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
     http_response_code(401);
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


if($role !="company"){
     http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Yetkisiz erisim']);
    exit;
}


// Şirket bilgisi
$company_id = getUserCompany($user_id);
if (!$company_id) {
    echo json_encode(['success' => false, 'message' => 'Şirket bulunamadı']);
    exit;
}

// İstek gövdesi JSON ise al
$rawInput = file_get_contents('php://input');
$jsonInput = null;
if (!empty($rawInput)) {
    $try = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $jsonInput = $try;
    }
}

$action = null;
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (is_array($jsonInput) && isset($jsonInput['action'])) {
    $action = $jsonInput['action'];
}

// CREATE or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    try {
        require_once __DIR__ . '/../system/config.php';

        if ($action === 'create') {
            $departure_city = trim($jsonInput['departure_city'] ?? '');
            $destination_city = trim($jsonInput['destination_city'] ?? '');
            $price = (float)($jsonInput['price'] ?? 0);
            $departure_date = trim($jsonInput['departure_date'] ?? '');
            $departure_time = trim($jsonInput['departure_time'] ?? '');
            $arrival_date = trim($jsonInput['arrival_date'] ?? '');
            $arrival_time = trim($jsonInput['arrival_time'] ?? '');
            $capacity = (int)($jsonInput['capacity'] ?? 0);

            if (!$departure_city || !$destination_city || $price <= 0 || !$departure_date || !$departure_time || (!$arrival_time) || $capacity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Eksik veya geçersiz alanlar']);
                exit;
            }

            $departure_datetime = $departure_date . ' ' . $departure_time;
            // arrival_date verilmişse onu kullan, yoksa departure_date ile oluştur
            $current_datetime = date('Y-m-d H:i:s');
            $arrival_datetime = ($arrival_date ? $arrival_date : $departure_date) . ' ' . $arrival_time;
            $departure_city_formatted = ucfirst($departure_city);
            $destination_city_formatted = ucfirst($destination_city);
            $insert_sql = 'INSERT INTO Trips (company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity,created_date) 
                           VALUES (:company_id, :departure_city, :destination_city, :departure_time, :arrival_time, :price, :capacity, :created_date)';
            $stmt = $db->prepare($insert_sql);
            $ok = $stmt->execute([
                ':company_id' => $company_id,
                ':departure_city' => $departure_city_formatted,
                ':destination_city' => $destination_city_formatted,
                ':departure_time' => $departure_datetime,
                ':arrival_time' => $arrival_datetime,
                ':price' => $price,
                ':capacity' => $capacity,
                ':created_date' => $current_datetime
            ]);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Sefer oluşturuldu']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sefer oluşturulamadı']);
            }
            exit;
        }

        if ($action === 'update') {
            $trip_id = (int)($jsonInput['trip_id'] ?? 0);
            $departure_city = trim($jsonInput['departure_city'] ?? '');
            $destination_city = trim($jsonInput['destination_city'] ?? '');
            $price = (float)($jsonInput['price'] ?? 0);
            $departure_date = trim($jsonInput['departure_date'] ?? '');
            $departure_time = trim($jsonInput['departure_time'] ?? '');
            $arrival_time = trim($jsonInput['arrival_time'] ?? '');
            $capacity = (int)($jsonInput['capacity'] ?? 0);

            if ($trip_id <= 0 || !$departure_city || !$destination_city || $price <= 0 || !$departure_date || !$departure_time || !$arrival_time || $capacity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Eksik veya geçersiz alanlar']);
                exit;
            }

            // Seferin şirkete ait olduğunu doğrula
            $check_sql = 'SELECT id FROM Trips WHERE id = :trip_id AND company_id = :company_id';
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->execute([':trip_id' => $trip_id, ':company_id' => $company_id]);
            if (!$check_stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Bu seferi düzenleme yetkiniz yok']);
                exit;
            }

            $departure_datetime = $departure_date . ' ' . $departure_time;
            $arrival_datetime = $departure_date . ' ' . $arrival_time;

            $update_sql = 'UPDATE Trips 
                           SET departure_city = :departure_city,
                               destination_city = :destination_city,
                               departure_time = :departure_time,
                               arrival_time = :arrival_time,
                               price = :price,
                               capacity = :capacity
                           WHERE id = :trip_id AND company_id = :company_id';
            $update_stmt = $db->prepare($update_sql);
            $ok = $update_stmt->execute([
                ':departure_city' => $departure_city,
                ':destination_city' => $destination_city,
                ':departure_time' => $departure_datetime,
                ':arrival_time' => $arrival_datetime,
                ':price' => $price,
                ':capacity' => $capacity,
                ':trip_id' => $trip_id,
                ':company_id' => $company_id
            ]);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Sefer güncellendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sefer güncellenemedi']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Bilinmeyen işlem']);
        exit;
    } catch (Exception $e) {
        error_log('companyAdmin action error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Sunucu hatası']);
        exit;
    }
}

// Varsayılan: listeleme ve kullanıcı bilgisi
$companyInfo = getCompany($company_id);
echo json_encode([
    'success' => true,
    'username' => $username,
    'company_id' => $company_id,
    'company_name' => $companyInfo
]);
exit;
