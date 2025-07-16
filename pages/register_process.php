<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// โค้ดส่วนที่เหลือของคุณ // <-- บรรทัดนี้ควรถูกลบออกไป เพราะมันคือคอมเมนต์ที่ไม่ใช่โค้ด

// register_process.php

// 1. เชื่อมต่อฐานข้อมูล (ควรใช้ไฟล์ config แยก)
$servername = "localhost";
$username = "root"; // ใช้ user root ในการพัฒนา
$password = "ppgdmild"; // รหัสผ่าน root ที่คุณตั้งไว้
$dbname = "eat_near_non"; // ชื่อฐานข้อมูลที่คุณจะใช้ (เช่น aron_non)

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// ตั้งค่า charset เป็น utf8mb4 เพื่อรองรับภาษาไทย
$conn->set_charset("utf8mb4");

// กำหนด Path/URL ของรูปโปรไฟล์เริ่มต้น (Default Profile Picture)
$default_profile_pic_url = '../static/images/profile.png'; 

// *** สำคัญ: ควรตั้งชื่อโฟลเดอร์โปรเจกต์โดยไม่มีช่องว่าง เช่น DeliciousNearNonthaburi ***
// เพื่อหลีกเลี่ยงปัญหาใน URL หรือ Path
// เช่น E:\xampp\htdocs\DeliciousNearNonthaburi\static\images\profile.png

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. ดึงข้อมูลจาก $_POST
    $firstname = $_POST['reg_firstname'];
    $surname = $_POST['reg_lastname'];
    $email = $_POST['reg_email'];
    $phone = isset($_POST['reg_tel']) ? $_POST['reg_tel'] : NULL;
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    $food_types = isset($_POST['food_type']) ? $_POST['food_type'] : [];
    $agree_terms = isset($_POST['agreeTerms']) ? TRUE : FALSE;

    // 2. ตรวจสอบและยืนยันข้อมูล
    $errors = [];
    if (empty($firstname)) { $errors[] = "กรุณากรอกชื่อจริง"; }
    if (empty($surname)) { $errors[] = "กรุณากรอกนามสกุล"; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง"; }
    if (strlen($password) < 6) { $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร"; }
    if ($password !== $confirm_password) { $errors[] = "รหัสผ่านไม่ตรงกัน"; }
    if (empty($food_types)) { $errors[] = "กรุณาเลือกประเภทอาหารที่ชอบอย่างน้อย 1 ประเภท"; }
    if (!$agree_terms) { $errors[] = "กรุณายอมรับข้อตกลงและเงื่อนไข"; }

    // ตรวจสอบอีเมลซ้ำ (สำคัญมาก!)
    $check_email_stmt = $conn->prepare("SELECT user_id FROM AppUser WHERE email = ?");
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    if ($check_email_result->num_rows > 0) {
        $errors[] = "อีเมลนี้ถูกใช้ในการลงทะเบียนแล้ว กรุณาใช้อีเมลอื่น";
    }
    $check_email_stmt->close();

    if (empty($errors)) {
        // 3. ประมวลผลข้อมูล: แฮชรหัสผ่าน
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // --- ส่วนที่เพิ่ม/แก้ไขสำหรับ Email Verification ---
        $email_verified_status = 0; // ตั้งเป็น 0 หรือ FALSE เพราะยังไม่ได้รับการยืนยัน
        $verification_code = rand(100000, 999999); // สร้างรหัส OTP 6 หลัก
        $code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes')); // รหัสหมดอายุใน 30 นาที
        // ----------------------------------------------------

        // 4. บันทึกข้อมูลลงฐานข้อมูล AppUser พร้อมกับ verification_code, code_expiry
        $stmt_user = $conn->prepare("INSERT INTO AppUser (firstname, surname, email, phone, password_hash, email_verified, verification_code, code_expiry, profile_picture_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_user->bind_param("sssssisss", $firstname, $surname, $email, $phone, $hashed_password, $email_verified_status, $verification_code, $code_expiry, $default_profile_pic_url);

        if ($stmt_user->execute()) {
            $user_id = $stmt_user->insert_id; // ได้ user_id ของผู้ใช้ที่เพิ่งสร้าง

            // 5. บันทึกประเภทอาหารที่ชอบลงใน UserFoodTypePreference
            if (!empty($food_types)) {
                $stmt_fav = $conn->prepare("INSERT INTO UserFoodTypePreference (user_id, food_type_id) VALUES (?, ?)");
                foreach ($food_types as $food_type_id_from_form) {
                    $food_type_id_int = (int)$food_type_id_from_form;
                    $stmt_fav->bind_param("ii", $user_id, $food_type_id_int);
                    $stmt_fav->execute();
                }
                $stmt_fav->close();
            }

            // ส่ง JSON Response กลับไปให้ JavaScript
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'ลงทะเบียนสำเร็จ! กรุณาตรวจสอบอีเมลของคุณเพื่อยืนยันบัญชี',
                'email' => $email,
                'verification_code' => $verification_code
            ]);
            exit();

        } else {
            // Error ในการบันทึก AppUser
            $errors[] = "เกิดข้อผิดพลาดในการลงทะเบียน: " . $conn->error;
        }
        $stmt_user->close();
    }

    // ถ้ามี Error, ส่ง JSON Response กลับไปแจ้ง Error
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode("\n", $errors)
        ]);
        exit();
    }
} else {
    // ถ้าไม่ได้ส่งแบบ POST มา
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้โดยตรง']);
    exit();
}

$conn->close();
// ไม่มี ?> ปิดท้ายไฟล์