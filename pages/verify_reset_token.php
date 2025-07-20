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
    die("Connection failed: " . $conn->connect_error);
}

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$is_token_valid = false;
$message = "";

if (!empty($email) && !empty($token)) {
    // 2. ตรวจสอบ Token ในฐานข้อมูล
    $stmt = $conn->prepare("SELECT user_id, reset_token_expires_at FROM AppUser WHERE email = ? AND reset_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $expires_at = strtotime($user['reset_token_expires_at']);
        $current_time = time();

        if ($current_time < $expires_at) {
            $is_token_valid = true;
            // Token ถูกต้องและยังไม่หมดอายุ, อนุญาตให้เปลี่ยนรหัสผ่าน
        } else {
            $message = "ลิงก์รีเซ็ตรหัสผ่านหมดอายุแล้ว กรุณาลองใหม่อีกครั้ง";
        }
    } else {
        $message = "ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง หรือมีการใช้งานไปแล้ว";
    }
    $stmt->close();
} else {
    $message = "ลิงก์รีเซ็ตรหัสผ่านไม่สมบูรณ์";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันรีเซ็ตรหัสผ่าน - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" href="../css/reset_password.css?v=<?php echo time(); ?>">
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="container">
        <div class="right-column">
            <h1 class="LoginTitle"><?php echo $is_token_valid ? "ตั้งรหัสผ่านใหม่" : "เกิดข้อผิดพลาด"; ?></h1>
            <p class="subtitle">
                <?php 
                    if ($is_token_valid) {
                        echo "กรุณากรอกรหัสผ่านใหม่ของคุณ";
                    } else {
                        echo $message;
                    }
                ?>
            </p>

            <?php if ($is_token_valid): ?>
            <form class="reset-password-form" method="post" action="reset_password.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="new-password" class="LoginLabel">รหัสผ่านใหม่</label>
                        <span class="error-message" id="new-password-error">กรุณากรอกรหัสผ่านใหม่ (6 ตัวขึ้นไป)</span>
                    </div>
                    <input type="password" id="new-password" name="new_password" placeholder="รหัสผ่านใหม่..." required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="confirm-new-password" class="LoginLabel">ยืนยันรหัสผ่านใหม่</label>
                        <span class="error-message" id="confirm-new-password-error">รหัสผ่านไม่ตรงกัน</span>
                    </div>
                    <input type="password" id="confirm-new-password" name="confirm_new_password" placeholder="ยืนยันรหัสผ่านใหม่..." required autocomplete="new-password">
                </div>

                <div class="form-bottom-actions"> 
                    <input type="submit" value="รีเซ็ตรหัสผ่าน" class="button1">
                </div>
            </form>
            <?php else: ?>
            <div class="register-link-container">
                <p>กลับไปหน้า <a href="forgot_pass.php" class="register-link">ลืมรหัสผ่าน?</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <br><br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <?php if ($is_token_valid): ?>
    <script src="../js/reset_password.js?v=<?php echo time(); ?>"></script> 
    <?php endif; ?>
</body>
</html>