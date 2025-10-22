<?php

require_once __DIR__ . '/../system/config.php'; 


function getCompanies() {
    global $db;



try {
        $stmt = $db->prepare(
            'SELECT id,name, logo_path
             FROM Bus_Company'
        );

        $stmt->execute();

        // CHANGE THIS LINE: Use PDO::FETCH_ASSOC to get key/value pairs
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC); 


        return $companies ?: [];
    } catch (PDOException $e) {
        // ...
        return [];
    }
}
