<?php
session_start(); // เริ่ม Session เพื่อเข้าถึงข้อมูล Session ที่มีอยู่

// เชื่อมต่อฐานข้อมูลสำหรับจัดการ remember_me_tokens
$servername = "localhost";
$username = "root";
$password = "ppgdmild"; // เปลี่ยนเป็นรหัสผ่านจริงของคุณ
$dbname = "eat_near_non";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    // ในสถานการณ์จริง คุณอาจจะบันทึก error ลง log แทนที่จะแสดงให้ผู้ใช้เห็น
    // และอาจจะยังคงทำการ logout Session แม้ database connection จะล้มเหลว
    error_log("Database connection failed for logout: " . $conn->connect_error);
    // ไม่จำเป็นต้อง exit() ทันที เพราะเรายังต้องการล้าง session/cookies
}

// 1. ลบ Remember Me Token ออกจากฐานข้อมูล (ถ้ามี)
if (isset($_COOKIE['remember_user'])) {
    $token_from_cookie = $_COOKIE['remember_user'];
    list($selector, $validator) = explode('.', $token_from_cookie);

    if (isset($conn) && $conn->ping()) { // ตรวจสอบว่าเชื่อมต่อ DB สำเร็จก่อนใช้
        $stmt = $conn->prepare("DELETE FROM remember_me_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
}

// 2. ลบ Remember Me Cookie ออกจากเบราว์เซอร์
// ตั้งเวลาหมดอายุเป็นอดีต เพื่อให้เบราว์เซอร์ลบ Cookie ทันที
setcookie('remember_user', '', [
    'expires' => time() - 3600, // หมดอายุไปแล้ว 1 ชั่วโมง
    'path' => '/',
    'httponly' => true, // ป้องกันการเข้าถึงจาก JavaScript
    'secure' => true,  // ส่งผ่าน HTTPS เท่านั้น (แนะนำอย่างยิ่งใน production)
    'samesite' => 'Lax' // ป้องกัน CSRF
]);

// 3. ลบข้อมูล Session ทั้งหมด
$_SESSION = array(); // ล้างตัวแปร Session ทั้งหมด

// 4. ทำลาย Session
session_destroy();

// 5. เปลี่ยนเส้นทางผู้ใช้ไปยังหน้าหลัก หรือหน้า Login
header("Location: index.php");
exit(); // สำคัญมาก: เพื่อให้แน่ใจว่าไม่มีโค้ดใดๆ ทำงานต่อหลัง redirect
?>