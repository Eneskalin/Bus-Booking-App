<?php
header('Content-Type: application/json');
require_once '../system/function.php'; 

if (!isset($db) || !($db instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantısı mevcut değil.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$tripId = null;

if (isset($data['tripid'])) {
    $tripId = intval($data['tripid']);
}
elseif (isset($_GET['tripid'])) {
    $tripId = intval($_GET['tripid']);
}

if (!$tripId) {
    echo json_encode(['success' => false, 'message' => 'tripid parametresi gerekli.']);
    exit;
}

try {
    $sql = "
        SELECT bs.seat_number 
        FROM Booked_Seats bs
        INNER JOIN Tickets t ON t.id = bs.ticket_id
        WHERE t.trip_id = :trip_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':trip_id' => $tripId]);
    $boughtSeats = $stmt->fetchAll(PDO::FETCH_COLUMN); 

    echo json_encode([
        'success' => true,
        'trip_id' => $tripId,
        'boughtSeats' => $boughtSeats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
