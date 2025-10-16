<?php
require_once __DIR__ . '/../system/config.php';
function getTicketInfo($id){
    global $db; 
    $stmt = $db->prepare('SELECT * FROM Trips WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result;
    } else {
        return null;
    }
}
