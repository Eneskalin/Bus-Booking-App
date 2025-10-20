<?php


function getTickets($user_id){
    global $db; 

    $sql = '
        SELECT 
            t.*,
            bus.name,
            bus.logo_path,
            tr.destination_city, 
            tr.departure_city, 
            tr.departure_time 
        FROM 
            Tickets t 
        LEFT JOIN 
            Trips tr ON t.trip_id = tr.id 
        LEFT JOIN 
            Bus_Company bus ON tr.company_id = bus.id
        WHERE 
            t.user_id = :user_id
    ';

    $stmt = $db->prepare($sql);
    
    $stmt->execute([':user_id' => $user_id]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    
    if ($results) {
        return $results;
    } else {
        return null; 
    }
}