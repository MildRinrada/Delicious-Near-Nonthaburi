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

$conn = "mysql:host=$host;dbname=$dbname;charset=$charset";

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error connecting to database: ' . $conn->connect_error,
        'field' => 'db_connection'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT user_id, firstname FROM AppUser WHERE email = ?");
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database prepare statement failed: ' . $conn->error,
            'field' => 'db_prepare_error'
        ]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $user_name = $user['firstname'];

        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update_stmt = $conn->prepare("UPDATE AppUser SET reset_token = ?, reset_token_expires_at = ? WHERE user_id = ?");
        if (!$update_stmt) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database update prepare statement failed: ' . $conn->error,
                'field' => 'db_update_prepare_error'
            ]);
            $stmt->close();
            $conn->close();
            exit();
        }
        $update_stmt->bind_param("ssi", $token, $expires_at, $user_id);

        if ($update_stmt->execute()) {
            $reset_link = "http://YOUR_DOMAIN_NAME/verify_reset_token.php?token=" . $token . "&email=" . urlencode($email);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'สร้างลิงก์รีเซ็ตสำเร็จ',
                'email' => $email,
                'user_name' => $user_name,
                'reset_link' => $reset_link
            ]);
            $update_stmt->close();
            $stmt->close();
            $conn->close();
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'มีข้อผิดพลาดในการบันทึกข้อมูลการรีเซ็ต กรุณาลองใหม่ภายหลัง: ' . $update_stmt->error,
                'field' => 'db_update_execute_error'
            ]);
            $update_stmt->close();
            $stmt->close();
            $conn->close();
            exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบอีเมลนี้ในระบบ',
            'field' => 'email'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. This file should only be accessed via POST.'
    ]);
    $conn->close();
    exit();
}
?>
