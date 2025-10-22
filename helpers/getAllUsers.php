<?php

require_once __DIR__ . '/../system/config.php'; 


function getUsers() {
    global $db;



try {
        $stmt = $db->prepare(
            'SELECT id,full_name,role,company_id
             FROM USER'
        );

        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC); 


        return $users ?: [];
    } catch (PDOException $e) {
        return [];
    }
}
