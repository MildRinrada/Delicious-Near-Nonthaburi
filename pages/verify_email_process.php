<?php
// verify_email_process.php

ini_set('display_errors', 1); // แสดง error สำหรับการ debug (ควรปิดใน production)
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "ppgdmild"; // ตรวจสอบรหัสผ่านของคุณอีกครั้ง
$dbname = "eat_near_non"; // ตรวจสอบชื่อฐานข้อมูลของคุณอีกครั้ง

// สร้าง Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบ Connection
if ($conn->connect_error) {
    header('Content-Type: application/json'); // ส่ง JSON Header
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// ตั้งค่า charset เป็น utf8mb4 เพื่อรองรับภาษาไทย
$conn->set_charset("utf8mb4");

// ตรวจสอบว่า request มาจาก method POST และมีข้อมูล verification_code ส่งมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    // 2. รับค่ารหัสยืนยันและอีเมลจากฟอร์ม
    $verification_code = trim($_POST['verification_code']);
    $user_email_from_form = trim($_POST['email']); // ดึงอีเมลจาก hidden input field

    // 3. เตรียมคำสั่ง SQL เพื่อค้นหาผู้ใช้ด้วยรหัสยืนยันและอีเมล
    // สำคัญ: เพิ่มเงื่อนไข WHERE email = ? เพื่อระบุผู้ใช้ให้ชัดเจนขึ้น
    $stmt = $conn->prepare("SELECT user_id, verification_code, code_expiry FROM AppUser WHERE verification_code = ? AND email = ? AND email_verified = 0");
    
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $verification_code, $user_email_from_form); // "ss" สำหรับ string สองตัว

    // 4. ประมวลผลคำสั่ง SQL
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // พบผู้ใช้ที่มีรหัสยืนยันนี้และอีเมลตรงกัน
        $user = $result->fetch_assoc();
        
        // ตรวจสอบเวลาหมดอายุของรหัส
        $current_time = new DateTime(); // เวลาปัจจุบัน
        $expiry_time = new DateTime($user['code_expiry']); // เวลาหมดอายุของรหัสใน DB

        if ($current_time <= $expiry_time) {
            // รหัสยังไม่หมดอายุ: ทำการยืนยันอีเมล
            $user_id_to_update = $user['user_id'];

            // เตรียมคำสั่ง SQL เพื่ออัปเดตสถานะ email_verified
            $update_stmt = $conn->prepare("UPDATE AppUser SET email_verified = 1, verification_code = NULL, code_expiry = NULL WHERE user_id = ?");
            
            if (!$update_stmt) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error preparing update statement: ' . $conn->error]);
                exit();
            }

            $update_stmt->bind_param("i", $user_id_to_update);

            if ($update_stmt->execute()) {
                // อัปเดตสำเร็จ
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'ยืนยันอีเมลสำเร็จแล้ว! คุณสามารถเข้าสู่ระบบได้']);
                exit();
            } else {
                // เกิดข้อผิดพลาดในการอัปเดต
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการยืนยันอีเมล กรุณาลองอีกครั้ง']);
                exit();
            }
            $update_stmt->close();
        } else {
            // รหัสหมดอายุแล้ว
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'รหัสยืนยันหมดอายุแล้ว กรุณาส่งรหัสใหม่']);
            exit();
        }
    } else {
        // ไม่พบผู้ใช้ที่มีรหัสยืนยันนี้ (รหัสไม่ถูกต้อง หรือถูกใช้ไปแล้ว)
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'รหัสยืนยันไม่ถูกต้อง']);
        exit();
    }

    $stmt->close(); // ปิด statement
} else {
    // หากเข้าถึงหน้านี้โดยตรงโดยไม่ได้ Submit ฟอร์ม
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้ได้โดยตรง กรุณาลงทะเบียนก่อน']);
    exit();
}

$conn->close();
?>