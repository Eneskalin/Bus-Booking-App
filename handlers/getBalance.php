<?php
require_once __DIR__ . '/../system/config.php';
function getBalance($id){
    global $db; 
    $stmt = $db->prepare('SELECT balance FROM User WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['balance'];
    } else {
        return null;
    }
}
