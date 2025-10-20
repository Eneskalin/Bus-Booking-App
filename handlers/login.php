<?php
require '../vendor/autoload.php';
require '../system/function.php'; 

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    
    error_log("Dotenv Yükleme Hatası: " . $e->getMessage());
    
    send_json_response("error", "Sunucu konfigürasyon hatası.");
}


$secret_key = $_ENV['JWT_SECRET_KEY'];


function generateJWT(int $user_id, string $full_name, string $role): string
{
    global $secret_key;
    
    $timestamp = time();
    $expiration_time = $timestamp + (60 * 60 * 3); 

    $payload = [
        'iat' => $timestamp,
        'nbf' => $timestamp,
        'exp' => $expiration_time,

        'data' => [
            'user_id' => $user_id,
            'username' => $full_name,
            'role' => $role
        ]
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');
    return $jwt;
}

function send_json_response($status, $message, $redirect = null, $delay = 1, $token = null) 
{
    header('Content-Type: application/json');
    $response = [
        'status' => $status,
        'message' => $message,
    ];

    if ($redirect) {
        $response['redirect'] = $redirect;
        $response['delay'] = $delay;
    }
    
    if ($token) {
        $response['token'] = $token;
    }

    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'signup') {
    

    $full_name = post('full_name');
    $email = post('email');
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($full_name) || empty($email) || empty($password) || empty($password_confirm)) {
        send_json_response("error", "Lütfen tüm alanları doldurun.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_json_response("error", "Geçerli bir e-posta adresi girin.");
    } elseif ($password !== $password_confirm) {
        send_json_response("error", "Şifreler eşleşmiyor.");
    } else {
        try {
            global $db;
            $checkUser = $db->prepare("SELECT id FROM User WHERE email = :email");
            $checkUser->execute([':email' => $email]);

            if ($checkUser->rowCount()) {
                send_json_response("error", "Bu e-posta adresi zaten kayıtlı.");
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $created_at = date('Y-m-d H:i:s');


                $insert = $db->prepare("INSERT INTO User (full_name, email, role, password, created_at) VALUES (:fn, :e, :r, :p, :c)");
                $result = $insert->execute([
                    ':fn' => $full_name,
                    ':e' => $email,
                    ':r' => 'user',
                    ':p' => $hashed_password,
                    ':c' => $created_at
                ]);

                if ($result) {
                    send_json_response("success", "Kayıt başarılı! Giriş yapabilirsiniz.", "login.php", 3);
                } else {
                    send_json_response("error", "Kayıt sırasında bir hata oluştu.");
                }
            }
        } catch (PDOException $e) {
            error_log("Kayıt Hatası: " . $e->getMessage());
            send_json_response("error", "Veritabanı hatası oluştu.");
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {

    $email = post('email');
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        send_json_response("error", "Lütfen tüm alanları doldurun.");
    } else {
        try {
            global $db; // PDO nesnesine erişim
            $userQuery = $db->prepare("SELECT id, full_name, role, password FROM User WHERE email = :email");
            $userQuery->execute([':email' => $email]);
            $user = $userQuery->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    
                    $jwt_token = generateJWT($user['id'], $user['full_name'], $user['role']);

                    // JWT Token'ı JSON yanıtına ekleyerek döndür
                    send_json_response("success", "Giriş başarılı! Yönlendiriliyorsunuz...", "index.php", 1, $jwt_token);

                } else {
                    send_json_response("error", "Hatalı e-posta veya şifre.");
                }
            } else {
                send_json_response("error", "Hatalı e-posta veya şifre.");
            }
        } catch (PDOException $e) {
            error_log("Giriş Hatası: " . $e->getMessage());
            send_json_response("error", "Veritabanı hatası oluştu.");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    send_json_response("error", "Geçersiz işlem isteği.");
}

?>