<?php
// get_restaurants.php

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล (กรุณาแก้ไขเป็นข้อมูลจริงของคุณ) ---
$host = 'localhost';
$db = 'eat_near_non'; // ตรวจสอบชื่อฐานข้อมูลของคุณให้ถูกต้อง
$user = 'root';      // ตรวจสอบชื่อผู้ใช้ฐานข้อมูลของคุณให้ถูกต้อง
$pass = 'ppgdmild'; // ตรวจสอบรหัสผ่านฐานข้อมูลของคุณให้ถูกต้อง
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // บันทึก Error ลง log เพื่อดูภายหลัง (แนะนำ)
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed. Please try again later.']);
    exit();
}

// --- รับค่า Filter จาก Query String ---
$filter_district = $_GET['district'] ?? '';
$filter_food_type = $_GET['food_type'] ?? '';

// --- คำนวณวันและเวลาปัจจุบันสำหรับสถานะเปิด/ปิด ---
// PHP's date('w') returns: 0 (for Sunday) through 6 (for Saturday)
$current_day_of_week = date('w');
// Current time in HH:MM:SS format
$current_time = date('H:i:s');

// --- DEBUGGING: ตรวจสอบเวลาปัจจุบันที่ Server รับรู้ ---
// สามารถ uncomment บรรทัดข้างล่างนี้เพื่อดูค่าในเบราว์เซอร์เมื่อเข้าถึง get_restaurants.php โดยตรง
// echo "Current Day (0=Sun, 6=Sat): " . $current_day_of_week . "<br>";
// echo "Current Time: " . $current_time . "<br>";
// exit(); // หยุดการทำงานชั่วคราวเพื่อดูค่า debug เท่านั้น

// --- สร้าง SQL Query หลักสำหรับการดึงข้อมูลร้านอาหาร ---
$sql = "
    SELECT
        r.restaurant_id,
        r.restaurant_name,
        r.rating_avg,
        r.rating_count,
        r.district,
        -- รวมชื่อประเภทอาหาร
        GROUP_CONCAT(DISTINCT ft.food_type_name SEPARATOR ', ') AS food_types,
        -- ดึง URL รูปภาพสูงสุด 4 รูป (ใช้ Subquery)
        (
            SELECT GROUP_CONCAT(ri.image_url ORDER BY ri.is_primary DESC, ri.image_id ASC)
            FROM restaurantimage AS ri
            WHERE ri.restaurant_id = r.restaurant_id
            LIMIT 4
        ) AS image_urls,
        roh.open_time,
        roh.close_time,
        roh.is_closed
    FROM
        restaurant AS r
    LEFT JOIN
        restaurantfoodtype AS rft ON r.restaurant_id = rft.restaurant_id
    LEFT JOIN
        foodtype AS ft ON rft.food_type_id = ft.food_type_id
    LEFT JOIN
        restaurant_opening_hours AS roh ON r.restaurant_id = roh.restaurant_id AND roh.day_of_week = :current_day_of_week
    WHERE
        1 = 1
";

$params = [
    ':current_day_of_week' => $current_day_of_week
];

// เพิ่มเงื่อนไข Filter ตามอำเภอ
if (!empty($filter_district)) {
    $sql .= " AND r.district = :district";
    $params[':district'] = $filter_district;
}

// เพิ่มเงื่อนไข Filter ตามประเภทอาหาร
if (!empty($filter_food_type)) {
    $sql .= " AND ft.food_type_name = :food_type";
    $params[':food_type'] = $filter_food_type;
}

// --- GROUP BY และ ORDER BY ---
$sql .= "
    GROUP BY
        r.restaurant_id, r.restaurant_name, r.rating_avg, r.rating_count, r.district,
        roh.open_time, roh.close_time, roh.is_closed
    ORDER BY
        r.restaurant_name ASC
    LIMIT 25;
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $restaurants_data = $stmt->fetchAll();

    $results = [];
    foreach ($restaurants_data as $restaurant) {
        // --- ประมวลผล URL รูปภาพ ---
        $images = [];
        if (!empty($restaurant['image_urls'])) {
            $images = explode(',', $restaurant['image_urls']);
            $images = array_map('trim', $images); // ลบช่องว่างที่อาจมี
        }

        // --- คำนวณสถานะเปิด/ปิด ---
        $status = 'ไม่ทราบสถานะ';

        // DEBUGGING: ตรวจสอบค่าเวลาเปิด-ปิดของร้านแต่ละร้าน และเวลาปัจจุบัน
        // หากต้องการ debug เฉพาะร้านใดร้านหนึ่ง
        // if ($restaurant['restaurant_name'] === 'Pizza Hut (พิซซ่าฮัท)') {
        //     error_log("--- Debug for " . $restaurant['restaurant_name'] . " ---");
        //     error_log("DB Open Time: " . ($restaurant['open_time'] ?? 'NULL'));
        //     error_log("DB Close Time: " . ($restaurant['close_time'] ?? 'NULL'));
        //     error_log("Current Time (from server): " . $current_time);
        //     error_log("Is Closed Flag: " . $restaurant['is_closed']);
        //     error_log("Raw Comparison: " . ($current_time >= $restaurant['open_time'] ? 'TRUE' : 'FALSE') . " && " . ($current_time <= $restaurant['close_time'] ? 'TRUE' : 'FALSE'));
        // }


        if (isset($restaurant['is_closed']) && $restaurant['is_closed'] == 1) {
            $status = 'ปิดถาวร'; // ชัดเจนขึ้นว่าปิดเพราะอะไร
        } else if (empty($restaurant['open_time']) || empty($restaurant['close_time'])) {
            $status = 'ไม่ระบุเวลาทำการ'; // หรือข้อมูลเวลาไม่ครบ
        } else {
            // เปรียบเทียบเวลาโดยตรง (string comparison ทำงานได้ดีกับ HH:MM:SS)
            if ($current_time >= $restaurant['open_time'] && $current_time <= $restaurant['close_time']) {
                $status = 'เปิดอยู่';
            } else {
                $status = 'ปิด (นอกเวลาทำการ)';
            }
        }

        $results[] = [
            'id'           => $restaurant['restaurant_id'],
            'name'         => $restaurant['restaurant_name'],
            'rating_avg'   => $restaurant['rating_avg'],
            'rating_count' => $restaurant['rating_count'],
            'district'     => $restaurant['district'],
            'food_types'   => $restaurant['food_types'],
            'status'       => $status,
            'images'       => $images
        ];
    }

    echo json_encode(['success' => true, 'restaurants' => $results]);

} catch (\PDOException $e) {
    // บันทึก Error ลง log เพื่อดูภายหลัง (แนะนำ)
    error_log("Query failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Query failed. Please try again later.']);
}
?>