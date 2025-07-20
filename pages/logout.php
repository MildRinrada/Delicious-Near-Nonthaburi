<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? 'ppgdmild';
$dbname = $_ENV['DB_NAME'] ?? 'eat_near_non';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    error_log("Database connection failed for logout: " . $conn->connect_error);
}

// เริ่ม session ก่อนทำงานกับ session
session_start();

// ตรวจสอบ cookie remember_user
if (isset($_COOKIE['remember_user'])) {
    $token_from_cookie = $_COOKIE['remember_user'];
    if (strpos($token_from_cookie, '.') !== false) {
        list($selector, $validator) = explode('.', $token_from_cookie);

        if ($conn->ping()) {
            $stmt = $conn->prepare("DELETE FROM remember_me_tokens WHERE selector = ?");
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// ลบ cookie remember_user
setcookie('remember_user', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Lax'
]);

// ล้าง session และทำลาย session
$_SESSION = array();
session_destroy();

// รีไดเรกต์กลับหน้าแรก
header("Location: index.php");
exit();
?>
