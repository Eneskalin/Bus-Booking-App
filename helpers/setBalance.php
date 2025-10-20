<?php
require_once __DIR__ . '/../system/config.php';

function setBalance($user_id, $price) {
    global $db;

    if (!$db) {
        error_log("HATA: Veritabanı bağlantısı yok!");
        throw new Exception('Veritabanı bağlantısı kurulamadı.');
    }

    try {
        $stmt = $db->prepare('
            UPDATE user 
            SET balance = balance - :price 
            WHERE id = :user_id
        ');

        $result = $stmt->execute([
            ':user_id' => $user_id,
            ':price' => $price
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("Bütçe güncellenemedi - SQL Error: " . json_encode($errorInfo));
            throw new Exception('Bütçe güncellenemedi.');
        }

    } catch (PDOException $e) {     
        error_log("Balance güncellenemedi (PDO): " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        
        throw new Exception('Bütçe veritabanına kaydedilemedi: ' . $e->getMessage());
    }
}
?>
