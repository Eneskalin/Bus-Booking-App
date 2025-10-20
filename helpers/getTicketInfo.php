<?php
require_once __DIR__ . '/../system/config.php';
function getTicketInfo($id){
    global $db; 
    $sql = '
        SELECT 
            t.*, 
            tr.departure_city, 
            tr.destination_city, 
            tr.departure_time,
            bus.name,
            bus.logo_path
        FROM Tickets t
        LEFT JOIN Trips tr ON t.trip_id = tr.id
        LEFT JOIN Bus_Company bus ON tr.company_id = bus.id
        WHERE t.id = :id
    ';
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result;
    } else {
        return null;
    }
}
