<?php

require_once __DIR__ . '/../system/config.php'; 

function getCoupons($company_id) {
    global $db;

    if (!isset($db) || !$db instanceof PDO) {
        error_log('getCoupons: $db yok veya PDO değil');
        return [];
    }

    try {
        $stmt = $db->prepare(
            'SELECT *
             FROM Coupons
             WHERE company_id = :company_id'
        );

        $stmt->execute([':company_id' => $company_id]);

        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $coupons ?: [];
    } catch (PDOException $e) {
        error_log('getCoupons DB error: ' . $e->getMessage());
        return [];
    }
}

?>