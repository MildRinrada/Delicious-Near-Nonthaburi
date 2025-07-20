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
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_email = $_POST['old_email'];
    $new_email = $_POST['new_email'];

    $errors = [];

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "กรุณากรอกอีเมลใหม่ที่ถูกต้อง";
    }

    $check_email_stmt = $conn->prepare("SELECT user_id FROM AppUser WHERE email = ? AND email != ?");
    $check_email_stmt->bind_param("ss", $new_email, $old_email);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    if ($check_email_result->num_rows > 0) {
        $errors[] = "อีเมลใหม่นี้ถูกใช้โดยผู้ใช้อื่นแล้ว กรุณาใช้อีเมลอื่น";
    }
    $check_email_stmt->close();

    if (empty($errors)) {
        $verification_code = rand(100000, 999999);
        $code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $email_verified_status = 0;

        $update_stmt = $conn->prepare("UPDATE AppUser SET email = ?, email_verified = ?, verification_code = ?, code_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sisss", $new_email, $email_verified_status, $verification_code, $code_expiry, $old_email);

        if ($update_stmt->execute()) {
            // Redirect แทนการ echo JSON
            header("Location: verify_email.php?email=$new_email&code=$verification_code");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดตอีเมล: " . $conn->error;
        }
        $update_stmt->close();
    }

    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode("\n", $errors),
            'old_email' => $old_email
        ]);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้โดยตรง']);
    exit();
}

$conn->close();
?>
