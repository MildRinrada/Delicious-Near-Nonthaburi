<?php

// 1. เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root"; // ใช้ user root ในการพัฒนา
$password = "ppgdmild"; // รหัสผ่าน root ที่คุณตั้งไว้
$dbname = "eat_near_non"; // ชื่อฐานข้อมูลของคุณ

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json'); // กำหนด Content-Type เป็น JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error connecting to database: ' . $conn->connect_error, // เพิ่มรายละเอียด error เพื่อ debug
        'field' => 'db_connection'
    ]);
    exit(); // สำคัญ: หยุดการทำงานทันที
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 3. ตรวจสอบอีเมลในฐานข้อมูล
    // ตรวจสอบให้แน่ใจว่าชื่อตารางคือ 'AppUser' และคอลัมน์คือ 'email', 'user_id', 'firstname'
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

        // 4. สร้าง Reset Token และ Token Expiry Time
        $token = bin2hex(random_bytes(32)); 
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); 

        // 5. บันทึก Token ลงในฐานข้อมูล
        // ตรวจสอบให้แน่ใจว่าคอลัมน์ 'reset_token' และ 'reset_token_expires_at' มีอยู่ในตาราง 'AppUser'
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

            header('Content-Type: application/json'); // กำหนด Content-Type เป็น JSON
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
            exit(); // สำคัญ: หยุดการทำงานทันที

        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'มีข้อผิดพลาดในการบันทึกข้อมูลการรีเซ็ต กรุณาลองใหม่ภายหลัง: ' . $update_stmt->error, // เพิ่มรายละเอียด error
                'field' => 'db_update_execute_error'
            ]);
            $update_stmt->close();
            $stmt->close();
            $conn->close();
            exit(); // สำคัญ: หยุดการทำงานทันที
        }
    } else {
        // ไม่พบอีเมลในระบบ
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบอีเมลนี้ในระบบ',
            'field' => 'email' 
        ]);
        $stmt->close();
        $conn->close();
        exit(); // สำคัญ: หยุดการทำงานทันที
    }
} else {
    // หากเข้าถึงไฟล์นี้โดยตรง ไม่ได้ผ่าน POST
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. This file should only be accessed via POST.'
    ]);
    $conn->close();
    exit(); // สำคัญ: หยุดการทำงานทันที
}
?>