<?php

require_once __DIR__ . '/../system/config.php'; 


function getSeats($trip_id) {
    global $db;

    if (!isset($db) || !$db instanceof PDO) {
        error_log('getSeats: $db yok veya PDO değil');
        return [];
    }

    try {
     $stmt = $db->prepare(
            'SELECT bs.seat_number
             FROM Booked_Seats bs
             INNER JOIN Tickets t ON bs.ticket_id = t.id
             WHERE t.trip_id = :trip_id
             ORDER BY bs.seat_number ASC'
        );

        $stmt->execute([':trip_id' => $trip_id]);

        $seats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

         $seats = array_map('intval', $seats);

        return $seats ?: [];
    } catch (PDOException $e) {
        error_log('getSeats DB error: ' . $e->getMessage());
        return [];
    }
}
