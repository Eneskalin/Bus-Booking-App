<?php
header('Content-Type: application/json');

require_once '../auth/verify_token.php';

$required_files = [
    __DIR__ . '/../helpers/getCoupon.php',
    __DIR__ . '/../helpers/getUserCompany.php',
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

// Token doğrula
$result = verifyJWT($token);
if (!$result['valid']) {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];

// Kullanıcının şirket ID'sini al
$company_id = getUserCompany($user_id);

// Şirket ID kontrolü
if (!$company_id) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Kullanıcıya ait şirket bulunamadı.'
    ]);
    exit;
}

// Kuponları al
$coupons = getCoupons($company_id);

// Başarılı yanıt
echo json_encode([
    'status' => 'success',
    'data' => [
        'coupons' => $coupons,
        'count' => count($coupons)
    ]
]);

?>