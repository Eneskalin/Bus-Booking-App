<?php
require_once __DIR__ . '/../system/config.php';

function getCompany($id){
    global $db; 
    
    $sql_company = 'SELECT * FROM Bus_Company WHERE id = :id';
    $stmt = $db->prepare($sql_company);
    $stmt->execute([':id' => $id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        return null;
    }
    
    $sql_trips = '
        SELECT 
            id,
            departure_city, 
            destination_city, 
            departure_time,
            arrival_time,
            price,
            capacity
        FROM Trips 
        WHERE company_id = :company_id
    ';
    $stmt = $db->prepare($sql_trips);
    $stmt->execute([':company_id' => $id]);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $company['trips'] = $trips;
    
    return $company;
}