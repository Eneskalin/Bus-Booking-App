<?php
require_once __DIR__ . '/../system/config.php';

function setSeat($ticket_id, $seat) { 
    global $db;

    if (!$db) {
        error_log("HATA: Veritabanı bağlantısı yok!");
        throw new Exception('Veritabanı bağlantısı kurulamadı.');
    }

    try {
        $stmt = $db->prepare('
            INSERT INTO Booked_Seats (ticket_id, seat_number, created_at) 
            VALUES (:ticket_id, :seat_number, CURRENT_TIMESTAMP)
        ');

        $result = $stmt->execute([
            ':ticket_id' => $ticket_id,    
            ':seat_number' => $seat        
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("Koltuk eklenemedi - SQL Error: " . json_encode($errorInfo));

            return false; 
        }

        error_log("Koltuk başarıyla eklendi. Ticket ID: " . $ticket_id . ", Koltuk No: " . $seat);
        
        return true; 

    } catch (PDOException $e) {    
        error_log("Koltuk eklenemedi (PDO): " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        

        throw new Exception('Koltuk veritabanına kaydedilemedi: ' . $e->getMessage());
    }
}
?>