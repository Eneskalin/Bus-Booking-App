
<?php
require_once __DIR__ . '/../system/config.php';

function setCoupon($company_id, $expire_date,$usageLimit, $discount_percentage = 10) {
    global $db;

    $coupon_code = generateRandomCouponCode(8);
    $created_at = date('Y-m-d H:i:s'); // Oluşturulma tarihini al

    if (!$db) {
        error_log("HATA: Veritabanı bağlantısı yok!");
        throw new Exception('Veritabanı bağlantısı kurulamadı.');
    }

    try {
        $stmt = $db->prepare('
            INSERT INTO Coupons (code,discount,company_id,usage_limit, expire_date, created_at) 
            VALUES (:code,:discount,:company_id,:usage_limit, :expire_date, :created_at)
        ');

        $result = $stmt->execute([
            ':code'         => $coupon_code,
            ':discount'      => $discount_percentage,
            ':company_id'    => $company_id,
            ':usage_limit'   => $usageLimit,
            ':expire_date'  => $expire_date,
            ':created_at'   => $created_at,
        ]);
        
        if (!$result) {
            error_log("HATA: Kupon veritabanına eklenemedi.");
            return false; 
        }
        
        return $coupon_code;

    } catch (PDOException $e) {
        
        error_log("Veritabanı hatası: " . $e->getMessage());
        throw new Exception('Kupon oluşturulurken bir veritabanı hatası oluştu.');
    }
}

function generateRandomCouponCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    
    return $randomString;
}



?>