<?php
header('Content-Type: application/json');
require_once '../system/function.php';
require_once '../auth/verify_token.php';
require_once '../helpers/setCoupon.php';
require_once '../helpers/getUserCompany.php';

if (!isset($db) || !($db instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantısı mevcut değil.']);
    exit;
}

// =====================
// Header'dan token al
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
    echo json_encode(['success' => false, 'message' => 'Token bulunamadı. Lütfen giriş yapın.']);
    exit;
}

// =====================
// Token doğrula
// =====================
$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['success' => false, 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$role = $result['data']['role'];

if ($role !== 'company') {
    echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

$company_id=getUserCompany($user_id);

// =====================
// JSON verisini al
// =====================
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz JSON verisi.']);
    exit;
}

// Geçerlilik tarihi kontrolü
if (!isset($data['expire_date']) || empty($data['expire_date'])) {
    echo json_encode(['success' => false, 'message' => 'Geçerlilik tarihi gerekli.']);
    exit;
}

$expire_date = $data['expire_date'];
$discount_percentage = isset($data['discount_percentage']) ? (int)$data['discount_percentage'] : 10;
$usageLimit=$data['usageLimit'];

// İndirim oranı kontrolü
if ($discount_percentage < 1 || $discount_percentage > 100) {
    echo json_encode(['success' => false, 'message' => 'İndirim oranı 1-100 arasında olmalıdır.']);
    exit;
}



$today = date('Y-m-d');
if ($expire_date <= $today) {
    echo json_encode(['success' => false, 'message' => 'Geçerlilik tarihi bugünden sonra olmalıdır.']);
    exit;
}

try {
    // Kupon üret
    $coupon_code = setCoupon($company_id,$expire_date,$usageLimit,  $discount_percentage);
    
    if ($coupon_code) {
        echo json_encode([
            'success' => true,
            'message' => 'Kupon başarıyla oluşturuldu.',
            'coupon_code' => $coupon_code,
            'expire_date' => $expire_date,
            'discount_percentage' => $discount_percentage,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kupon oluşturulamadı.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
?>
