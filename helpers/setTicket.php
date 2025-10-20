<?php
require_once __DIR__ . '/../system/config.php';

function setTicketInfo($trip_id, $user_id, $price) {
    global $db;

    if (!$db) {
        error_log("HATA: Veritabanı bağlantısı yok!");
        throw new Exception('Veritabanı bağlantısı kurulamadı.');
    }

    try {
        $stmt = $db->prepare('
            INSERT INTO Tickets (trip_id, user_id, status, total_price, created_at) 
            VALUES (:trip_id, :user_id, :status, :total_price, CURRENT_TIMESTAMP)
        ');

        $result = $stmt->execute([
            ':trip_id' => $trip_id,
            ':user_id' => $user_id,
            ':status' => 'active',
            ':total_price' => $price
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("Ticket eklenemedi - SQL Error: " . json_encode($errorInfo));
            throw new Exception('Bilet kaydedilemedi.');
        }

        $ticket_id = $db->lastInsertId();
        error_log("✅ Ticket başarıyla eklendi. ID: " . $ticket_id . " | Trip: " . $trip_id . " | User: " . $user_id . " | Price: " . $price);
        
        return $ticket_id;

    } catch (PDOException $e) {     
        error_log("❌ Ticket eklenemedi (PDO): " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        
        throw new Exception('Bilet veritabanına kaydedilemedi: ' . $e->getMessage());
    }
}
?>