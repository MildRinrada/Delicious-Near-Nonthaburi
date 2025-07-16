<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// โค้ดส่วนที่เหลือของคุณ
?>

<?php
// resend_code_process.php

// ไม่ต้องใช้ namespace สำหรับ PHPMailer อีกต่อไป
// ไม่ต้อง include PHPMailer classes อีกต่อไป

header('Content-Type: application/json'); // บอกเบราว์เซอร์ว่าเราจะส่ง JSON กลับไป

// 1. เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root"; // เปลี่ยนเป็น username ฐานข้อมูลของคุณ
$password = "ppgdmild"; // เปลี่ยนเป็น password ฐานข้อมูลของคุณ
$dbname = "eat_near_non"; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบ Connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8mb4");

// ตรวจสอบว่า request มาจาก method POST และมีข้อมูล email ส่งมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $user_email = trim($_POST['email']);

    // 2. ค้นหาผู้ใช้จากอีเมล
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

        // 3. ตรวจสอบสถานะการยืนยันอีเมล
        if ($user['email_verified'] == 1) {
            echo json_encode(['success' => false, 'message' => 'อีเมลนี้ได้รับการยืนยันแล้ว ไม่จำเป็นต้องส่งรหัสใหม่']);
            $stmt->close();
            $conn->close();
            exit();
        }

        // ตรวจสอบว่าสามารถส่งรหัสซ้ำได้หรือไม่ (ยังไม่ได้ implement ในโค้ดตัวอย่าง)
        // if (isset($user['last_resend_timestamp'])) { // สมมติว่ามีคอลัมน์นี้
        //     $last_resend_time = new DateTime($user['last_resend_timestamp']);
        //     $current_time = new DateTime();
        //     $interval = $current_time->getTimestamp() - $last_resend_time->getTimestamp();
        //     if ($interval < 60) { // 60 วินาที
        //         echo json_encode(['success' => false, 'message' => 'กรุณารอสักครู่ก่อนส่งรหัสอีกครั้ง']);
        //         $stmt->close();
        //         $conn->close();
        //         exit();
        //     }
        // }

        // 4. สร้างรหัสยืนยันใหม่และเวลาหมดอายุ
        $new_verification_code = rand(100000, 999999);
        $new_code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes')); // หมดอายุใน 30 นาที

        // 5. อัปเดตฐานข้อมูลด้วยรหัสใหม่
        $update_stmt = $conn->prepare("UPDATE AppUser SET verification_code = ?, code_expiry = ? WHERE user_id = ?");
        if (!$update_stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare update statement failed: ' . $conn->error]);
            exit();
        }
        $update_stmt->bind_param("ssi", $new_verification_code, $new_code_expiry, $user['user_id']);

        if ($update_stmt->execute()) {
            // **ส่วนที่เปลี่ยน: ส่ง JSON Response กลับไปให้ JavaScript**
            echo json_encode([
                'success' => true,
                'message' => 'รหัสยืนยันใหม่ถูกสร้างและบันทึกแล้ว',
                'email' => $user_email, // ส่งอีเมลกลับไป
                'verification_code' => $new_verification_code // ส่งรหัสยืนยันใหม่กลับไป
            ]);
            exit(); // หยุดการทำงานของ PHP
            // --------------------------------------------------------------------
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
?>