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

// **ไม่ต้องใช้** set_charset กับ PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember_me = (isset($_POST['rememberMe']) && $_POST['rememberMe'] === 'true'); 

    if (empty($email) || empty($password_input)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกอีเมลและรหัสผ่าน']);
        exit();
    }

    // ใช้ prepare & execute ของ PDO
    $stmt = $conn->prepare("SELECT user_id, firstname, surname, password_hash, email_verified FROM AppUser WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (!$user['email_verified']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'อีเมลของคุณยังไม่ได้รับการยืนยัน กรุณาตรวจสอบอีเมลหรือลงทะเบียนใหม่อีกครั้ง', 'unverified' => true]);
            exit();
        }

        if (password_verify($password_input, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_surname'] = $user['surname']; 
            $_SESSION['logged_in'] = true;

            if ($remember_me) {
                $selector = bin2hex(random_bytes(16));
                $validator = bin2hex(random_bytes(32));
                $token = $selector . '.' . base64_encode($validator);
                $hashed_validator = hash('sha256', $validator);

                $expiry_time = time() + (30 * 24 * 60 * 60);
                $expiry_date = date('Y-m-d H:i:s', $expiry_time);

                $insert_token_stmt = $conn->prepare("INSERT INTO remember_me_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE selector = VALUES(selector), hashed_validator = VALUES(hashed_validator), expires_at = VALUES(expires_at)");
                $insert_token_stmt->execute([$user['user_id'], $selector, $hashed_validator, $expiry_date]);

                setcookie(
                    'remember_user', 
                    $token, 
                    [
                        'expires' => $expiry_time,
                        'path' => '/',
                        'httponly' => true,
                        'secure' => true,
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
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
