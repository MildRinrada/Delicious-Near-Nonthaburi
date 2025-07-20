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

$default_profile_pic_url = '../static/images/profile.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $firstname = $_POST['reg_firstname'] ?? '';
    $surname = $_POST['reg_lastname'] ?? '';
    $email = $_POST['reg_email'] ?? '';
    $phone = $_POST['reg_tel'] ?? null;
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['reg_confirm_password'] ?? '';
    $food_types = isset($_POST['food_type']) ? (array)$_POST['food_type'] : [];
    $agree_terms = isset($_POST['agreeTerms']) ? true : false;

    $errors = [];

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty(trim($firstname))) { $errors[] = "กรุณากรอกชื่อจริง"; }
    if (empty(trim($surname))) { $errors[] = "กรุณากรอกนามสกุล"; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง"; }
    if (strlen($password) < 6) { $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร"; }
    if ($password !== $confirm_password) { $errors[] = "รหัสผ่านไม่ตรงกัน"; }
    if (empty($food_types)) { $errors[] = "กรุณาเลือกประเภทอาหารที่ชอบอย่างน้อย 1 ประเภท"; }
    if (!$agree_terms) { $errors[] = "กรุณายอมรับข้อตกลงและเงื่อนไข"; }

    // ตรวจสอบอีเมลซ้ำในฐานข้อมูล
    $check_email_stmt = $conn->prepare("SELECT user_id FROM AppUser WHERE email = ?");
    $check_email_stmt->execute([$email]);
    if ($check_email_stmt->fetch()) {
        $errors[] = "อีเมลนี้ถูกใช้ในการลงทะเบียนแล้ว กรุณาใช้อีเมลอื่น";
    }

    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => implode("\n", $errors)]);
        exit();
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // กำหนดสถานะการยืนยันอีเมลและรหัสสำหรับยืนยัน
    $email_verified_status = 0;
    $verification_code = rand(100000, 999999);
    $code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    try {
        // เริ่ม transaction
        $conn->beginTransaction();

        // เพิ่มข้อมูลผู้ใช้ใหม่ในตาราง AppUser
        $stmt_user = $conn->prepare("INSERT INTO AppUser (firstname, surname, email, phone, password_hash, email_verified, verification_code, code_expiry, profile_picture_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_user->execute([
            $firstname,
            $surname,
            $email,
            $phone,
            $hashed_password,
            $email_verified_status,
            $verification_code,
            $code_expiry,
            $default_profile_pic_url
        ]);

        $user_id = $conn->lastInsertId();

        // บันทึกประเภทอาหารที่ชอบในตาราง UserFoodTypePreference
        if (!empty($food_types)) {
            $stmt_fav = $conn->prepare("INSERT INTO UserFoodTypePreference (user_id, food_type_id) VALUES (?, ?)");
            foreach ($food_types as $food_type_id_from_form) {
                $food_type_id_int = (int)$food_type_id_from_form;
                $stmt_fav->execute([$user_id, $food_type_id_int]);
            }
        }

        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'ลงทะเบียนสำเร็จ! กรุณาตรวจสอบอีเมลของคุณเพื่อยืนยันบัญชี',
            'email' => $email,
            'verification_code' => $verification_code
        ]);
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการลงทะเบียน: ' . $e->getMessage()
        ]);
        exit();
    }

} else {
    // กรณีเข้าถึงโดยตรง (ไม่ใช่ POST)
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเข้าถึงหน้านี้โดยตรง']);
    exit();
}
?>
