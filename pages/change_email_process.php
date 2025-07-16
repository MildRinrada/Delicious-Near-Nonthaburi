<?php
$servername = "localhost";
$username = "root"; // ใช้ user root ในการพัฒนา
$password = "ppgdmild"; // รหัสผ่าน root ที่คุณตั้งไว้
$dbname = "eat_near_non"; // ชื่อฐานข้อมูลของคุณ

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // ส่งข้อความ error กลับไปเป็น JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}
// ตั้งค่า charset เป็น utf8mb4 เพื่อรองรับภาษาไทย
$conn->set_charset("utf8mb4");

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ดึงข้อมูลอีเมลเก่าและอีเมลใหม่จากฟอร์ม
    $old_email = $_POST['old_email'];
    $new_email = $_POST['new_email'];

    $errors = []; // อาร์เรย์สำหรับเก็บข้อผิดพลาด

    // ตรวจสอบรูปแบบอีเมลใหม่
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "กรุณากรอกอีเมลใหม่ที่ถูกต้อง";
    }

    // ตรวจสอบว่าอีเมลใหม่ซ้ำกับอีเมลอื่นในระบบหรือไม่ (ยกเว้นตัวผู้ใช้เอง)
    // เราต้องแน่ใจว่าอีเมลใหม่ไม่ชนกับอีเมลของผู้ใช้คนอื่น
    $check_email_stmt = $conn->prepare("SELECT user_id FROM AppUser WHERE email = ? AND email != ?");
    $check_email_stmt->bind_param("ss", $new_email, $old_email);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    if ($check_email_result->num_rows > 0) {
        $errors[] = "อีเมลใหม่นี้ถูกใช้โดยผู้ใช้อื่นแล้ว กรุณาใช้อีเมลอื่น";
    }
    $check_email_stmt->close();

    if (empty($errors)) {
        // เตรียมข้อมูลใหม่สำหรับ User
        // สร้างรหัส OTP ใหม่
        $verification_code = rand(100000, 999999); 
        // ตั้งเวลาหมดอายุใหม่ (เช่น 30 นาทีจากเวลาปัจจุบัน)
        $code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes')); 
        // ตั้งสถานะเป็นยังไม่ยืนยัน เพราะมีการเปลี่ยนอีเมล
        $email_verified_status = 0; 

        // 4. อัปเดตข้อมูลอีเมลและสถานะการยืนยันในฐานข้อมูล
        // เราจะอัปเดต row ของผู้ใช้ที่มี $old_email
        $update_stmt = $conn->prepare("UPDATE AppUser SET email = ?, email_verified = ?, verification_code = ?, code_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sisss", $new_email, $email_verified_status, $verification_code, $code_expiry, $old_email);

        if ($update_stmt->execute()) {
            // **ส่วนที่เปลี่ยน: ส่ง JSON Response กลับไปให้ JavaScript**
            header('Content-Type: application/json'); // บอกเบราว์เซอร์ว่านี่คือ JSON
            echo json_encode([
                'success' => true,
                'message' => 'อีเมลของคุณได้รับการอัปเดตและรหัสยืนยันใหม่ถูกสร้างแล้ว! กรุณาตรวจสอบอีเมล ' . $new_email . ' เพื่อยืนยันบัญชี',
                'email' => $new_email, // ส่งอีเมลใหม่กลับไป
                'verification_code' => $verification_code // ส่งรหัสยืนยันใหม่กลับไป
            ]);
            exit(); // หยุดการทำงานของ PHP หลังจากส่ง JSON
            // --------------------------------------------------------------------

        } else {
            // เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดตอีเมล: " . $conn->error;
        }
        $update_stmt->close();
    }

    // ถ้ามี Error, ส่ง JSON Response กลับไปแจ้ง Error
    if (!empty($errors)) {
        header('Content-Type: application/json'); // บอกเบราว์เซอร์ว่านี่คือ JSON
        echo json_encode([
            'success' => false,
            'message' => implode("\n", $errors), // รวม error เป็น string เดียว
            'old_email' => $old_email // ส่งอีเมลเก่ากลับไปเผื่อ JavaScript ต้องการใช้
        ]);
        exit();
    }
} else {
    // ถ้าไม่ได้ส่งแบบ POST มา แสดงว่าเข้าหน้านี้โดยตรง
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้โดยตรง']);
    exit();
}

$conn->close();
?>