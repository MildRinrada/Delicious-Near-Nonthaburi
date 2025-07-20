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

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// -- ไม่ต้อง set_charset กับ PDO --

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $verification_code = trim($_POST['verification_code']);
    $user_email_from_form = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id, verification_code, code_expiry FROM AppUser WHERE verification_code = ? AND email = ? AND email_verified = 0");
    $stmt->execute([$verification_code, $user_email_from_form]);
    $user = $stmt->fetch();

    if ($user) {
        $current_time = new DateTime();
        $expiry_time = new DateTime($user['code_expiry']);

        if ($current_time <= $expiry_time) {
            $update_stmt = $conn->prepare("UPDATE AppUser SET email_verified = 1, verification_code = NULL, code_expiry = NULL WHERE user_id = ?");
            if ($update_stmt->execute([$user['user_id']])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'ยืนยันอีเมลสำเร็จแล้ว! คุณสามารถเข้าสู่ระบบได้']);
                exit();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการยืนยันอีเมล กรุณาลองอีกครั้ง']);
                exit();
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'รหัสยืนยันหมดอายุแล้ว กรุณาส่งรหัสใหม่']);
            exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'รหัสยืนยันไม่ถูกต้อง']);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้ได้โดยตรง กรุณาลงทะเบียนก่อน']);
    exit();
}

$conn->close();
?>
