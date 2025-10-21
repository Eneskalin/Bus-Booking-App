<?php
require_once __DIR__ . '/../system/config.php';

function getTripInfo($tripId) {
	global $db;
	$sql = '
		SELECT 
			tr.*, 
			bus.name AS company_name,
			bus.logo_path
		FROM Trips tr
		LEFT JOIN Bus_Company bus ON tr.company_id = bus.id
		WHERE tr.id = :trip_id
	';
	$stmt = $db->prepare($sql);
	$stmt->execute([':trip_id' => $tripId]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($result) {
		return $result;
	} else {
		return null;
	}
}

?>

