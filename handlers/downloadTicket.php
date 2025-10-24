<?php
use Dompdf\Dompdf;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

require '../vendor/autoload.php';
require '../system/function.php';
require_once '../auth/verify_token.php';
require_once '../helpers/getTicketInfo.php';
require_once '../helpers/getBookedSeats.php';

// =====================
// 3️ Header'dan token al
// =====================
$token = null;
$headers = function_exists('getallheaders') ? getallheaders() : [];

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($headers['authorization'])) {
    $token = str_replace('Bearer ', '', $headers['authorization']);
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
}

if (!$token) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Token bulunamadı. Lütfen giriş yapın.']);
    exit;
}

// =====================
// 4️ Token doğrula
// =====================
$result = verifyJWT($token);
if (!$result['valid']) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

$user_id = $result['data']['user_id'];
$username = $result['data']['username'];
$role = $result['data']['role'];

// =====================
// 5️⃣ Gönderilen JSON verisini al
// =====================
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['ticket'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
    exit;
}

// =====================
// 6 Token ile ticket kullanicisini karsilastir
// =====================
$ticket_id = (int) $data['ticket'];
$ticketInfo = getTicketInfo($ticket_id);
$seat_numbers_array = getBookedSeats($ticket_id); // Array döner

if (!$ticketInfo) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Bilet bulunamadı.']);
    exit;
}

if ($ticketInfo['user_id'] != $user_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erisim.']);
    exit;
}

// =====================
// 7 Verileri hazırla
// =====================
// Tarih formatını düzenle
$trip_date = date('d/m/Y', strtotime($ticketInfo['trip_datetime']));
$trip_time = date('H:i', strtotime($ticketInfo['trip_datetime']));

// Koltuk numaralarını string'e çevir
$seat_numbers = !empty($seat_numbers_array) 
    ? implode(', ', $seat_numbers_array) 
    : 'Belirtilmemiş';

// Yolcu sayısını hesapla
$passenger_count = !empty($seat_numbers_array) 
    ? count($seat_numbers_array) 
    : 1;

// Toplam tutarı hesapla
$total_amount = $ticketInfo['total_price'] ?? 0;
$formatted_amount = number_format($total_amount, 2, ',', '.') . ' TL';

// Logo yolunu kontrol et
$logo_path = !empty($ticketInfo['logo_path']) 
    ? '../' . $ticketInfo['logo_path'] 
    : '../img/Kapadokya.png';

$html = '
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>E-Bilet</title>

		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">

								</td>

								<td>
									E-Bilet #' . htmlspecialchars($ticketInfo['id']) . '
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									' . htmlspecialchars($ticketInfo['name'] ?? 'Cappadocia Tour, Inc.') . '<br />
									<br />
									' . htmlspecialchars($ticketInfo['destination_city']) . '
								</td>

								<td>
									' . htmlspecialchars($username) . '<br />
									Yolcu Sayısı: <span>' . $passenger_count . '</span><br />
									Koltuk: <span>' . htmlspecialchars($seat_numbers) . '</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td>Yolculuk Bilgileri</td>
					<td></td>
				</tr>

				<tr class="details">
					<td>Sefer:</td>
					<td>' . htmlspecialchars($ticketInfo['departure_city']) . ' - ' . htmlspecialchars($ticketInfo['destination_city']) . '</td>
				</tr>
				
				<tr class="details">
					<td>Firma:</td>
					<td>' . htmlspecialchars($ticketInfo['name'] ?? 'Belirtilmemiş') . '</td>
				</tr>
				
				<tr class="details">
					<td>Tarih:</td>
					<td>' . htmlspecialchars($trip_date) . '</td>
				</tr>

				<tr class="details">
					<td>Saat:</td>
					<td>' . htmlspecialchars($trip_time) . '</td>
				</tr>

				<tr class="heading">
					<td>Ödeme Bilgileri</td>
					<td>Tutar</td>
				</tr>

				<tr class="item">
					<td>Bilet Ücreti</td>
					<td>' . htmlspecialchars($formatted_amount) . '</td>
				</tr>

				<tr class="total">
					<td></td>
					<td>Toplam Tutar: ' . htmlspecialchars($formatted_amount) . '</td>
				</tr>
			</table>
		</div>
	</body>
</html>
';

// =====================
// 8️⃣ PDF Oluşturma ve İndirme
// =====================
$dompdf = new Dompdf();
$dompdf->getOptions()->set('defaultFont', 'DejaVu Sans');
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="bilet_' . $ticketInfo['id'] . '.pdf"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($dompdf->output()));

echo $dompdf->output();
exit;
?>