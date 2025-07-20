<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// ปิด output อื่นที่อาจหลุดออกมา
ob_start();

// ตั้งค่า Content-Type สำหรับ JSON
header('Content-Type: application/json');

// แนะนำให้ปิด error display (เพื่อไม่ให้หลุดออกมาใน response)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? 'ppgdmild';
$dbname = $_ENV['DB_NAME'] ?? 'eat_near_non';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $user_email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id, email_verified, code_expiry FROM AppUser WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['email_verified'] == 1) {
            echo json_encode(['success' => false, 'message' => 'อีเมลนี้ได้รับการยืนยันแล้ว ไม่จำเป็นต้องส่งรหัสใหม่']);
            $stmt->close();
            $conn->close();
            exit();
        }

        $new_verification_code = rand(100000, 999999);
        $new_code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $update_stmt = $conn->prepare("UPDATE AppUser SET verification_code = ?, code_expiry = ? WHERE user_id = ?");
        if (!$update_stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare update statement failed: ' . $conn->error]);
            exit();
        }
        $update_stmt->bind_param("ssi", $new_verification_code, $new_code_expiry, $user['user_id']);

        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'รหัสยืนยันใหม่ถูกสร้างและบันทึกแล้ว',
                'email' => $user_email,
                'verification_code' => $new_verification_code
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตรหัสยืนยันในฐานข้อมูลได้: ' . $conn->error]);
        }
        $update_stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบอีเมลในระบบ']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
ob_end_flush();
?>
