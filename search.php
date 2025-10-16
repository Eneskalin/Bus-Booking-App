<!DOCTYPE html>
<html>

<?php
include_once './system/function.php'; 

$from = isset($_GET['from']) ? trim($_GET['from']) : ''; 
$to = isset($_GET['to']) ? trim($_GET['to']) : ''; 
$date = isset($_GET['date']) ? trim($_GET['date']) : ''; 
$passengers = isset($_GET['passengers']) ? (int)trim($_GET['passengers']) : 1; 

$from_lower = mb_strtolower($from, 'UTF-8'); 
$to_lower = mb_strtolower($to, 'UTF-8'); 

try {
    $sql = 'SELECT t.*, bc.name AS company, bc.logo_path AS company_logo
            FROM Trips t
            LEFT JOIN Bus_Company bc ON t.company_id = bc.id
            WHERE 1=1';

    $params = [];

    if ($from !== '') {
        $sql .= " AND LOWER(t.departure_city) LIKE :from";
        $params[':from'] = '%' . $from_lower . '%'; 
    }

    if ($to !== '') {
        $sql .= " AND LOWER(t.destination_city) LIKE :to";
        $params[':to'] = '%' . $to_lower . '%'; 
    }

    if ($date !== '') {
        $sql .= ' AND date(t.departure_time) = :date'; 
        $params[':date'] = $date; 
    }
    
    $sql .= ' AND t.capacity >= :passengers';
    $params[':passengers'] = $passengers;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $filtered_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="notification is-danger">Veritabanı Hatası: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $filtered_trips = [];
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seferler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

</head>

<body>

    <?php include_once './inc/navbar.php'; ?>

    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-9">
                    <div id="notification-area"></div>
                    
                    <div class="trip-results">
                        <?php if (empty($filtered_trips)): ?>
                            <div class="notification is-warning">Seçimlerinize uygun sefer bulunamadı.</div>
                        <?php else: ?>
                            <?php foreach ($filtered_trips as $trip):
                                $price = (float) ($trip['price'] ?? 0);
                                $total_price = $price * $passengers;

                                $company = $trip['company'] ?? 'Bilinmeyen Firma';
                                $company_logo = $trip['company_logo'] ?? './logo.png';
                                $departure_city = $trip['departure_city'] ?? 'Kalkış Yeri Bilinmiyor';
                                $destination_city = $trip['destination_city'] ?? ($trip['arrival_city'] ?? 'Varış Yeri Bilinmiyor');

                                $departure_time_raw = $trip['departure_time'] ?? '';
                                $arrival_time_raw = $trip['arrival_time'] ?? '';

                                $departure_time = '';
                                $arrival_time = '';
                                $duration = '';

                                try {
                                    if (!empty($departure_time_raw)) {
                                        $dt_dep = new DateTime($departure_time_raw);
                                        $departure_time = $dt_dep->format('H:i');
                                    }
                                    if (!empty($arrival_time_raw)) {
                                        $dt_arr = new DateTime($arrival_time_raw);
                                        $arrival_time = $dt_arr->format('H:i');
                                    }

                                    if (!empty($departure_time_raw) && !empty($arrival_time_raw)) {
                                        $interval = $dt_dep->diff($dt_arr);
                                        $hours = (int)$interval->format('%h') + ((int)$interval->format('%d') * 24);
                                        $minutes = (int)$interval->format('%i');

                                        if ($hours > 0) {
                                            $duration = $hours . ' saat';
                                            if ($minutes > 0) {
                                                $duration .= ' ' . $minutes . ' dk';
                                            }
                                        } else {
                                            $duration = $minutes . ' dk';
                                        }
                                    }
                                } catch (Exception $e) {
                                    $departure_time = $departure_time_raw;
                                    $arrival_time = $arrival_time_raw;
                                    $duration = $trip['duration'] ?? '';
                                }

                                $price_formatted = number_format($price, 0, ',', '.');
                                $total_price_formatted = number_format($total_price, 0, ',', '.');
                                ?>
                                <div class="trip-card has-background-light">
                                    <div class="columns is-vcentered">
                                        <div class="column is-2">
                                            <div class="company-logo">
                                                <img src="<?php echo htmlspecialchars($company_logo); ?>"
                                                    alt="<?php echo htmlspecialchars($company); ?>">
                                                <p class="company-name has-text-primary">
                                                    <?php echo htmlspecialchars($company); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="column is-3">
                                            <div class="trip-info">
                                                <h3 class="departure-city has-text-link">
                                                    <?php echo htmlspecialchars($departure_city); ?>
                                                </h3>
                                                <p class="departure-time has-text-link is-size-4">
                                                    <?php echo htmlspecialchars($departure_time); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="column is-2">
                                            <div class="duration-info has-text-centered">
                                                <i class="bi bi-clock-fill has-text-primary is-size-5"></i>
                                                <p class="duration has-text-primary is-size-5">
                                                    <?php echo htmlspecialchars($duration); ?>
                                                </p>
                                                <div class="route-line"></div>
                                            </div>
                                        </div>
                                        <div class="column is-3">
                                            <div class="trip-info">
                                                <h3 class="arrival-city has-text-link is-size-5">
                                                    <?php echo htmlspecialchars($destination_city); ?>
                                                </h3>
                                                <p class="arrival-time has-text-link is-size-5">
                                                    <?php echo htmlspecialchars($arrival_time); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="column is-2">
                                            <div class="price-info">
                                                <p class="price has-text-primary">
                                                    ₺<?php echo $price_formatted; ?></p>
                                                <p class="price-per-person has-text-primary">Kişi başı</p>
                                                <p class="has-text-weight-bold has-text-primary">
                                                    Toplam: ₺<?php echo $total_price_formatted; ?>
                                                </p>
                                                <button 
                                                    class="button is-link is-fullwidth mt-2 buy-ticket-btn" 
                                                    data-trip-id="<?php echo htmlspecialchars($trip['id']); ?>"
                                                    data-total-price="<?php echo $total_price; ?>"
                                                    data-passengers="<?php echo $passengers; ?>">
                                                    <i class="bi bi-arrow-right mr-2"></i>
                                                    Devam Et
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer custom-footer">
        <div class="container">
            <div class="content has-text-centered">
                <div class="columns">
                    <div class="column">
                        <h4 class="has-text-white has-text-weight-bold">KapadokyaBus</h4>
                        <p class="has-text-white">Kapadokya'ya güvenli ve konforlu seyahat</p>
                    </div>
                    <div class="column">
                        <h5 class="has-text-white has-text-weight-bold">Hızlı Linkler</h5>
                        <a href="#" class="has-text-white">Seferler</a><br>
                        <a href="#" class="has-text-white">Hakkımızda</a><br>
                        <a href="#" class="has-text-white">İletişim</a>
                    </div>
                    <div class="column">
                        <h5 class="has-text-white has-text-weight-bold">İletişim</h5>
                        <p class="has-text-white">
                            <i class="bi bi-telephone mr-2"></i>
                            +90 384 123 45 67
                        </p>
                        <p class="has-text-white">
                            <i class="bi bi-envelope mr-2"></i>
                            info@kapadokyabus.com
                        </p>
                    </div>
                </div>
                <hr class="has-background-white">
                <p class="has-text-white">
                    © 2024 KapadokyaBus. Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </footer>


</body>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="./js/script.js"></script>
<script src="./js/navbar.js"></script>

</html>