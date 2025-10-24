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
    __DIR__ . '/../helpers/getAllCompanies.php',
    __DIR__ . '/../helpers/getAllUsers.php',
    __DIR__ . '/../helpers/getCoupon.php'
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
// 4 Token doğrula
// =====================
$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];


if ($role != "admin") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erisim']);
    exit;
}


// Sirketler
$companies = getCompanies();

// Kullanicilar
$users = getUsers();

//kuponlar
$coupons = getCoupons(0);




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
            $company_name = trim($jsonInput['name'] ?? '');
            $logo_path = trim($jsonInput['logo_path'] ?? '');

            if (!$company_name || !$logo_path) {
                echo json_encode(['success' => false, 'message' => 'Eksik veya geçersiz alanlar']);
                exit;
            }

            $insert_sql = 'INSERT INTO Bus_Company (name, logo_path) 
                           VALUES (:name, :logo_path)';
            $stmt = $db->prepare($insert_sql);
            $ok = $stmt->execute([
                ':name' => $company_name,
                ':logo_path' => $logo_path,

            ]);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Firma oluşturuldu']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Firma oluşturulamadı']);
            }
            exit;
        } else if ($action === 'authorize') {
            $user_id = trim($jsonInput['userId'] ?? '');
            $company_id = trim($jsonInput['companyId'] ?? '');

            if (!$user_id || !$company_id) {
                echo json_encode(['success' => false, 'message' => 'Eksik veya geçersiz kullanıcı veya firma ID\'si.']);
                exit;
            }

            $update_sql = 'UPDATE User 
                   SET role = "company", 
                       company_id = :company_id 
                   WHERE id = :user_id';

            $stmt = $db->prepare($update_sql);
            $ok = $stmt->execute([
                ':company_id' => $company_id,
                ':user_id' => $user_id
            ]);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Kullanıcıya başarıyla firma yetkisi atandı.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Yetki atama işlemi başarısız oldu.']);
            }
            exit;
        } else if ($action === 'generate') {
            $expire_date = trim($jsonInput['expire_date'] ?? '');
            $usageLimit = trim($jsonInput['usageLimit'] ?? '');
            $discountRate = trim($jsonInput['discountRate'] ?? '');
            $code = trim($jsonInput['code'] ?? '');
            $created_at = date('Y-m-d H:i:s');

            if (!$discountRate || !$usageLimit || !$expire_date || !$code) {
                echo json_encode(['success' => false, 'message' => 'Eksik veya geçersiz kullanıcı veya firma ID\'si.']);
                exit;
            }
            $insert_sql = 'INSERT INTO Coupons (code, discount, company_id, usage_limit, expire_date, created_at) 
                   VALUES (:code, :discount, 0, :usageLimit, :expire_date, :created_at)';

            $stmt = $db->prepare($insert_sql);
            $ok = $stmt->execute([
                ':code' => $code,
                ':discount' => $discountRate,
                ':usageLimit' => $usageLimit,
                ':expire_date' => $expire_date,
                ':created_at' => $created_at
            ]);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Kod oluşturuldu']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kod oluşturulamadı']);
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

echo json_encode([
    'success' => true,
    'companies' => $companies,
    'coupons' => $coupons,
    'users' => $users
]);
exit;
