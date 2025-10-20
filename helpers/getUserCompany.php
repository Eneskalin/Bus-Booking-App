<?php
require_once __DIR__ . '/../system/config.php';
function getUserCompany($id){
    global $db; 
    $stmt = $db->prepare('SELECT company_id FROM User WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['company_id'];
    } else {
        return null;
    }
}
