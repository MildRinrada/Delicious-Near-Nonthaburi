<?php
// reset_password.php

// 1. เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "ppgdmild"; // อย่าลืมเปลี่ยนเป็นรหัสผ่านจริงของคุณ
$dbname = "eat_near_non";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$status_class = ""; // สำหรับ CSS เช่น 'success' หรือ 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // 2. ตรวจสอบข้อมูลเบื้องต้น
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
        // 3. ตรวจสอบ Token ในฐานข้อมูลอีกครั้ง (สำคัญเพื่อความปลอดภัย)
        $stmt = $conn->prepare("SELECT user_id, reset_token_expires_at FROM AppUser WHERE email = ? AND reset_token = ?");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $expires_at = strtotime($user['reset_token_expires_at']);
            $current_time = time();

            if ($current_time < $expires_at) {
                // Token ถูกต้องและยังไม่หมดอายุ
                $user_id = $user['user_id'];
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน

                // 4. อัปเดตรหัสผ่านและล้าง Token
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
                $message = "ลิงก์รีเซ็ตรหัสผ่านหมดอายุแล้ว กรุณาส่งคำขอใหม่";
                $status_class = "error";
            }
        } else {
            $message = "ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง หรือมีการใช้งานไปแล้ว";
            $status_class = "error";
        }
        $stmt->close();
    }
} else {
    // เข้าถึงหน้านี้โดยตรงโดยไม่ส่ง POST
    header("Location: forgot_pass.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลลัพธ์รีเซ็ตรหัสผ่าน - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" href="../css/reset_password.css?v=<?php echo time(); ?>">
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