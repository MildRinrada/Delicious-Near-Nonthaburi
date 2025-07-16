<?php
// login_process.php

file_put_contents("debug_post.txt", print_r($_POST, true));

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "ppgdmild"; // อย่าลืมเปลี่ยนเป็นรหัสผ่านจริงของคุณ
$dbname = "eat_near_non";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // *** แก้ไขตรงนี้ ***
    // ตรวจสอบว่า 'rememberMe' ถูกส่งมาและค่าเป็น 'true' (สตริง)
    $remember_me = (isset($_POST['rememberMe']) && $_POST['rememberMe'] === 'true'); 
    // ถ้า $_POST['rememberMe'] ถูกส่งมา และมีค่าเป็นสตริง "true" เท่านั้น ถึงจะเป็นจริง

    if (empty($email) || empty($password_input)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกอีเมลและรหัสผ่าน']);
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, firstname, surname, password_hash, email_verified FROM AppUser WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_password_hash = $user['password_hash'];
        $is_email_verified = $user['email_verified'];

        if (!$is_email_verified) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'อีเมลของคุณยังไม่ได้รับการยืนยัน กรุณาตรวจสอบอีเมลหรือลงทะเบียนใหม่อีกครั้ง', 'unverified' => true]);
            exit();
        }

        if (password_verify($password_input, $stored_password_hash)) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_surname'] = $user['surname']; 
            $_SESSION['logged_in'] = true;

            // ส่วนนี้จะทำงานก็ต่อเมื่อ $remember_me เป็น true เท่านั้น
            if ($remember_me) { 
                $selector = bin2hex(random_bytes(16));
                $validator = bin2hex(random_bytes(32));
                $token = $selector . '.' . base64_encode($validator);
                $hashed_validator = hash('sha256', $validator);

                $expiry_time = time() + (30 * 24 * 60 * 60); 
                $expiry_date = date('Y-m-d H:i:s', $expiry_time);

                $insert_token_stmt = $conn->prepare("INSERT INTO remember_me_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE selector = VALUES(selector), hashed_validator = VALUES(hashed_validator), expires_at = VALUES(expires_at)");
                $insert_token_stmt->bind_param("isss", $user['user_id'], $selector, $hashed_validator, $expiry_date);
                $insert_token_stmt->execute();
                $insert_token_stmt->close();

                setcookie(
                    'remember_user', 
                    $token, 
                    [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,
                        'secure' => true, // ควรใช้ 'secure' ใน Production ถ้าใช้ HTTPS
                        'samesite' => 'Lax'
                    ]
                );
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ! กำลังนำทาง...', 'redirect' => 'index.php']); 
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ไม่พบอีเมลนี้ในระบบ']);
    }

    $stmt->close();
    $conn->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>