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

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";       // เก็บข้อความสถานะแสดงผล
$status_class = "";  // เก็บสถานะสำหรับ CSS เช่น success หรือ error

// ตรวจสอบว่าได้รับข้อมูลผ่าน POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // ตรวจสอบข้อมูลเบื้องต้นว่าครบถ้วนหรือไม่ และตรวจสอบรหัสผ่านใหม่
    if (empty($email) || empty($token) || empty($new_password) || empty($confirm_new_password)) {
        $message = "ข้อมูลไม่สมบูรณ์ กรุณาลองใหม่";
        $status_class = "error";
    } elseif ($new_password !== $confirm_new_password) {
        $message = "รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน";
        $status_class = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
        $status_class = "error";
    } else {
        // ตรวจสอบ token ในฐานข้อมูลว่าถูกต้องและไม่หมดอายุ
        $stmt = $conn->prepare("SELECT user_id, reset_token_expires_at FROM AppUser WHERE email = ? AND reset_token = ?");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $expires_at = strtotime($user['reset_token_expires_at']);
            $current_time = time();

            if ($current_time < $expires_at) {
                // Token ยังไม่หมดอายุ, ทำการอัปเดตรหัสผ่านใหม่ พร้อมล้าง token
                $user_id = $user['user_id'];
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE user_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);

                if ($update_stmt->execute()) {
                    $message = "รีเซ็ตรหัสผ่านสำเร็จ คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว";
                    $status_class = "success";
                } else {
                    $message = "มีข้อผิดพลาดในการอัปเดตรหัสผ่าน กรุณาลองใหม่";
                    $status_class = "error";
                }
                $update_stmt->close();
            } else {
                // Token หมดอายุแล้ว
                $message = "ลิงก์รีเซ็ตรหัสผ่านหมดอายุแล้ว กรุณาส่งคำขอใหม่";
                $status_class = "error";
            }
        } else {
            // ไม่พบ user หรือ token ไม่ถูกต้อง
            $message = "ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง หรือมีการใช้งานไปแล้ว";
            $status_class = "error";
        }
        $stmt->close();
    }
} else {
    // หากเข้าถึงหน้านี้โดยตรง (ไม่ใช่ POST) ให้รีไดเรกต์ไปหน้าลืมรหัสผ่าน
    header("Location: forgot_pass.php");
    exit();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ผลลัพธ์รีเซ็ตรหัสผ่าน - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/reset_password.css?v=<?php echo time(); ?>" />
    <style>
        .message-box {
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
        }
        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="container">
        <div class="right-column">
            <h1 class="LoginTitle">ผลลัพธ์การรีเซ็ตรหัสผ่าน</h1>
            <div class="message-box <?php echo $status_class; ?>">
                <?php echo $message; ?>
            </div>

            <div class="register-link-container">
                <?php if ($status_class == "success"): ?>
                    <p>กลับไปหน้า <a href="login.php" class="register-link">เข้าสู่ระบบ</a></p>
                <?php else: ?>
                    <p>กลับไปหน้า <a href="forgot_pass.php" class="register-link">ลืมรหัสผ่าน?</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <br><br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
</body>
</html>
