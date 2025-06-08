<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверяем, есть ли автолоадер
$autoload = __DIR__ . '/vendor/autoload.php';



require $autoload;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Загрузка .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
// Защита от прямого доступа
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен']);
    exit;
}

// Rate Limiting
session_start();
if (!isset($_SESSION['form_submit_time'])) {
    $_SESSION['form_submit_time'] = time();
} else {
    $time_diff = time() - $_SESSION['form_submit_time'];
    if ($time_diff < 10) {
        echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, подождите немного перед повторной отправкой']);
        exit;
    }
    $_SESSION['form_submit_time'] = time();
}






// reCAPTCHA проверка
if (empty($_POST['g-recaptcha-response'])) {
    echo json_encode(['status' => 'error', 'message' => 'Проверьте reCAPTCHA']);
    exit;
}

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'];
$recaptcha_response = $_POST['g-recaptcha-response'];

// Используем cURL вместо file_get_contents
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$recaptcha_url?secret=$recaptcha_secret&response=$recaptcha_response");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$recaptcha = json_decode($response);

if (!$recaptcha || !isset($recaptcha->success) || !$recaptcha->success) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка reCAPTCHA. Попробуйте снова.']);
    exit;
}



// Получаем данные
$email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''), ENT_QUOTES, 'UTF-8');
$firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''), ENT_QUOTES, 'UTF-8');
$middleName = htmlspecialchars(trim($_POST['middleName'] ?? ''), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$problem = htmlspecialchars(trim($_POST['problem'] ?? ''), ENT_QUOTES, 'UTF-8');
$additionalInfo = htmlspecialchars(trim($_POST['additionalInfo'] ?? ''), ENT_QUOTES, 'UTF-8');


if (empty($lastName) || empty($firstName) || empty($phone) || empty($problem)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Пожалуйста, заполните все обязательные поля: Фамилия, Имя, Телефон, Описание проблемы'
    ]);
    exit;
}


$mail = new PHPMailer(true);

try {
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USER'];
    $mail->Password   = $_ENV['MAIL_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    // Адрес отправителя и получателя
    $mail->setFrom($_ENV['MAIL_USER'], 'Truck Diesel Service');
    $mail->addAddress($_ENV['MAIL_USER']);

    // Содержание письма
    $mail->isHTML(false);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Новое сообщение клиента!';

    $mail->Body = "
    Запись на диагностику!\n
    Email:" . (!empty($email) ? $email : 'Не указано') . "\n
    Фамилия:" . (!empty($lastName) ? $lastName : 'Не указано') . "\n
    Имя:" . (!empty($firstName) ? $firstName : 'Не указано') . "\n
    Отчество:" . (!empty($middleName) ? $middleName : 'Не указано') . "\n
    Телефон:" . (!empty($phone) ? $phone : 'Не указано') . "\n
    Проблема:". (!empty($problem) ? $problem : 'Не указано') . "\n
    Дополнительная информация:"  . (!empty($additionalInfo) ? $additionalInfo : 'Не указано') . "\n
    ";

    // Отправка письма
    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Спасибо! Ваша заявка отправлена.']);
} catch (Exception $e) {
    error_log("Ошибка отправки письма: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка отправки письма']);
}
