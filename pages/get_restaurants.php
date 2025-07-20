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
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

// --- รับค่า Filter จาก Query String ---
$filter_districts   = isset($_GET['district']) ? (array) $_GET['district'] : [];
$filter_food_names  = isset($_GET['food_type']) ? (array) $_GET['food_type'] : [];
$search_name        = isset($_GET['restaurant_name']) ? trim($_GET['restaurant_name']) : '';
$filter_food_id     = isset($_GET['food_type_id']) ? (int)$_GET['food_type_id'] : null;
$sort               = $_GET['sort'] ?? 'default';

// --- ORDER BY ---
$orderBy = 'r.restaurant_name ASC';
if ($sort === 'rating_desc') {
    $orderBy = 'r.rating_avg DESC, r.restaurant_name ASC';
}

// --- วัน-เวลา ปัจจุบัน ---
$php_day = (int) date('w'); // 0=อาทิตย์ ... 6=เสาร์
$current_day = $php_day === 0 ? 7 : $php_day;
$current_time  = date('H:i:s');

// --- SQL ---
$sql = "
SELECT
    r.restaurant_id,
    r.restaurant_name,
    r.rating_avg,
    r.rating_count,
    r.district,
    GROUP_CONCAT(DISTINCT ft.food_type_name SEPARATOR ', ') AS food_type_name,
    (
        SELECT GROUP_CONCAT(ri.image_url ORDER BY ri.is_primary DESC, ri.image_id ASC)
        FROM restaurantimage AS ri
        WHERE ri.restaurant_id = r.restaurant_id
        LIMIT 4
    ) AS image_urls,
    roh.open_time,
    roh.close_time,
    roh.is_closed
FROM restaurant AS r
LEFT JOIN restaurantfoodtype AS rft ON r.restaurant_id = rft.restaurant_id
LEFT JOIN foodtype AS ft ON rft.food_type_id = ft.food_type_id
LEFT JOIN restaurant_opening_hours AS roh ON r.restaurant_id = roh.restaurant_id AND roh.day_of_week = :current_day
WHERE 1=1
";

$params = [':current_day' => $current_day];

// --- เงื่อนไขชื่อร้าน ---
if ($search_name !== '') {
    $sql .= " AND r.restaurant_name LIKE :search_name";
    $params[':search_name'] = "%{$search_name}%";
}

// --- เงื่อนไขเขต (district) ---
if (!empty($filter_districts)) {
    $placeholders = [];
    foreach ($filter_districts as $i => $district) {
        $key = ":district{$i}";
        $placeholders[] = $key;
        $params[$key] = $district;
    }
    $sql .= " AND r.district IN (" . implode(', ', $placeholders) . ")";
}

// --- เงื่อนไขชื่อประเภทอาหาร (food_type_name) ---
if (!empty($filter_food_names)) {
    $ft_placeholders = [];
    foreach ($filter_food_names as $i => $ftname) {
        $key = ":ftname{$i}";
        $ft_placeholders[] = $key;
        $params[$key] = $ftname;
    }
    $sql .= " AND ft.food_type_name IN (" . implode(', ', $ft_placeholders) . ")";
}

$sql .= " GROUP BY r.restaurant_id ORDER BY {$orderBy} LIMIT 25";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $r) {
        $imgs = $r['image_urls'] ? array_map('trim', explode(',', $r['image_urls'])) : [];
        error_log("Open time: {$r['open_time']}, Close time: {$r['close_time']}, is_closed: {$r['is_closed']}");

        // สถานะร้าน
        if ($r['is_closed']) {
            $status = 'ปิดถาวร';
        } elseif (!$r['open_time'] || !$r['close_time']) {
            $status = 'ไม่ระบุเวลาทำการ';
        } elseif ($current_time >= $r['open_time'] && $current_time <= $r['close_time']) {
            $status = 'เปิดอยู่';
        } else {
            $status = 'ปิด (นอกเวลาทำการ)';
        }

        $results[] = [
            'restaurant_id' => $r['restaurant_id'],
            'restaurant_name' => $r['restaurant_name'],
            'rating_avg' => is_numeric($r['rating_avg']) ? (float) $r['rating_avg'] : null,
            'rating_count' => is_numeric($r['rating_count']) ? (int) $r['rating_count'] : 0,
            'district' => $r['district'],
            'food_type_name' => $r['food_type_name'],
            'status' => $status,
            'images' => $imgs,
        ];
    }

    echo json_encode(['success' => true, 'restaurants' => $results]);
} catch (\PDOException $e) {
    error_log('Query failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Query failed.']);
}
