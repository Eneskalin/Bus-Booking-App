<?php
header('Content-Type: application/json');
require_once '../auth/verify_token.php';
require_once '../auth/generateDiscountToken.php'; // Yeni fonksiyonun olduğu dosya
require_once __DIR__ . '/../system/config.php';

$token = null;
$headers = function_exists('getallheaders') ? getallheaders() : [];

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($headers['authorization'])) {
    $token = str_replace('Bearer ', '', $headers['authorization']);
}

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token bulunamadı']);
    exit;
}

$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id']; 

$input = json_decode(file_get_contents('php://input'), true);
$coupon_code = isset($input['coupon_code']) ? trim($input['coupon_code']) : '';

if (empty($coupon_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Kupon kodu gerekli']);
    exit;
}

try {
    $stmt = $db->prepare('
        SELECT id, discount, company_id, usage_limit, expire_date, created_at
        FROM Coupons
        WHERE code = :code
    ');
    
    $stmt->execute([':code' => $coupon_code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        $error_message = 'Geçersiz kupon kodu';
        $discount_token = generateDiscountToken($user_id, $coupon_code, 'invalid_code');
        echo json_encode(['status' => 'error', 'message' => $error_message]);
        exit;
    }

    $today = date('Y-m-d');
    
    if ($coupon['expire_date'] < $today) {
        $error_message = 'Kupon süresi dolmuş';
        $discount_token = generateDiscountToken($user_id, $coupon_code, 'expired');
        echo json_encode(['status' => 'error', 'message' => $error_message, 'token' => $discount_token]);
        exit;
    }

    if ($coupon['usage_limit'] <= 0) {
        $error_message = 'Kupon kullanım limiti dolmuş';
        $discount_token = generateDiscountToken($user_id, $coupon_code, 'limit_reached');
        echo json_encode(['status' => 'error', 'message' => $error_message, 'token' => $discount_token]);
        exit;
    }


    $discount_token = generateDiscountToken($user_id, $coupon_code, 'valid');

    echo json_encode([
        'status' => 'success', 
        'message' => 'Kupon başarıyla doğrulandı', 
        'token' => $discount_token
    ]);

    
} catch (PDOException $e) {
    // Veritabanı hatası
    error_log('Kupon doğrulama hatası: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Kupon kontrol edilirken hata oluştu']);
}
?>