<?php

require_once __DIR__ . '/../system/config.php'; 


function getBookedSeats($ticket_id) {
    global $db;

    if (!isset($db) || !$db instanceof PDO) {
        error_log('getSeats: $db yok veya PDO değil');
        return [];
    }

    try {
     $stmt = $db->prepare(
            'SELECT seat_number
             FROM Booked_Seats 
             WHERE ticket_id = :ticket_id'
        );

        $stmt->execute([':ticket_id' => $ticket_id]);

        $seats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

         $seats = array_map('intval', $seats);

        return $seats ?: [];
    } catch (PDOException $e) {
        error_log('getSeats DB error: ' . $e->getMessage());
        return [];
    }
}
