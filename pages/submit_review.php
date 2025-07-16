<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error_message'] = 'กรุณาเข้าสู่ระบบเพื่อดำเนินการ.';
    header('Location: login.php');
    exit();
}

// ตรวจสอบว่าเป็น request แบบ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'ไม่พบการส่งข้อมูลที่ถูกต้อง.';
    header('Location: restaurants_list.php'); // หรือกลับไปหน้าที่เรียกมา
    exit();
}

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล ---
$host = 'localhost';
$db = 'eat_near_non';
$user = 'root';
$pass = 'ppgdmild';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES  => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    $_SESSION['error_message'] = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage();
    header('Location: restaurant_detail.php?id=' . ($_POST['restaurant_id'] ?? ''));
    exit();
}

// รับค่าที่จำเป็น
$restaurant_id = (int)($_POST['restaurant_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? ''; // 'add_review', 'update_review', 'delete_review'
$review_id = (int)($_POST['review_id'] ?? 0); // สำหรับการแก้ไข/ลบ

if ($restaurant_id <= 0) {
    $_SESSION['error_message'] = 'ไม่พบ ID ร้านอาหารที่ถูกต้อง.';
    header('Location: restaurants_list.php');
    exit();
}

// --- ฟังก์ชันสำหรับอัปเดตคะแนนเฉลี่ยและจำนวนรีวิว ---
function updateRestaurantRating($pdo, $restaurant_id) {
    $update_rating_sql = "
        SELECT AVG(rating) AS avg_rating, COUNT(review_id) AS review_count
        FROM restaurantreview
        WHERE restaurant_id = :restaurant_id;
    ";
    $stmt_update_rating = $pdo->prepare($update_rating_sql);
    $stmt_update_rating->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
    $stmt_update_rating->execute();
    $new_ratings = $stmt_update_rating->fetch();

    $avg_rating = round($new_ratings['avg_rating'], 1); // ปัดทศนิยม 1 ตำแหน่ง
    $review_count = $new_ratings['review_count'];

    // อัปเดตตาราง restaurant
    $update_restaurant_sql = "
        UPDATE restaurant
        SET rating_avg = :rating_avg, rating_count = :rating_count
        WHERE restaurant_id = :restaurant_id;
    ";
    $stmt_update_restaurant = $pdo->prepare($update_restaurant_sql);
    $stmt_update_restaurant->bindParam(':rating_avg', $avg_rating);
    $stmt_update_restaurant->bindParam(':rating_count', $review_count, PDO::PARAM_INT);
    $stmt_update_restaurant->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
    $stmt_update_restaurant->execute();
}

try {
    $pdo->beginTransaction(); // เริ่มต้น Transaction

    switch ($action) {
        case 'add_review':
            $rating = (int)($_POST['rating'] ?? 0);
            $review_text = trim($_POST['review_text'] ?? '');
            $review_date = date('Y-m-d H:i:s');

            // ตรวจสอบว่าผู้ใช้เคยรีวิวร้านนี้แล้วหรือไม่
            $check_sql = "SELECT COUNT(*) FROM restaurantreview WHERE restaurant_id = :restaurant_id AND user_id = :user_id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $check_stmt->execute();
            if ($check_stmt->fetchColumn() > 0) {
                $_SESSION['error_message'] = 'คุณได้รีวิวร้านนี้ไปแล้ว หากต้องการแก้ไขโปรดใช้ฟอร์มแก้ไข.';
                break; // ออกจาก switch
            }

            // ตรวจสอบความถูกต้องของข้อมูล
            if ($rating < 1 || $rating > 5) {
                $_SESSION['error_message'] = 'คะแนนรีวิวต้องอยู่ระหว่าง 1 ถึง 5 ดาว.';
                break;
            }
            if (empty($review_text)) {
                $_SESSION['error_message'] = 'กรุณาใส่ความคิดเห็นของคุณ.';
                break;
            }

            // บันทึกรีวิวใหม่
            $insert_sql = "
                INSERT INTO restaurantreview (restaurant_id, user_id, rating, review_date, text)
                VALUES (:restaurant_id, :user_id, :rating, :review_date, :text);
            ";
            $stmt_insert = $pdo->prepare($insert_sql);
            $stmt_insert->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt_insert->bindParam(':review_date', $review_date);
            $stmt_insert->bindParam(':text', $review_text);
            $stmt_insert->execute();

            $_SESSION['success_message'] = 'รีวิวของคุณถูกบันทึกเรียบร้อยแล้ว!';
            break;

        case 'update_review':
            // ต้องมี review_id และเป็นรีวิวของผู้ใช้ปัจจุบันเท่านั้น
            $rating = (int)($_POST['rating'] ?? 0);
            $review_text = trim($_POST['review_text'] ?? '');

            if ($review_id <= 0) {
                $_SESSION['error_message'] = 'ไม่พบ ID รีวิวที่ถูกต้องสำหรับการแก้ไข.';
                break;
            }
            if ($rating < 1 || $rating > 5) {
                $_SESSION['error_message'] = 'คะแนนรีวิวต้องอยู่ระหว่าง 1 ถึง 5 ดาว.';
                break;
            }
            if (empty($review_text)) {
                $_SESSION['error_message'] = 'กรุณาใส่ความคิดเห็นของคุณ.';
                break;
            }

            // อัปเดตรีวิว
            $update_sql = "
                UPDATE restaurantreview
                SET rating = :rating, review_date = :review_date, text = :text
                WHERE review_id = :review_id AND user_id = :user_id AND restaurant_id = :restaurant_id;
            ";
            $stmt_update = $pdo->prepare($update_sql);
            $stmt_update->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt_update->bindParam(':review_date', date('Y-m-d H:i:s')); // อัปเดตเวลาที่แก้ไข
            $stmt_update->bindParam(':text', $review_text);
            $stmt_update->bindParam(':review_id', $review_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt_update->execute();

            if ($stmt_update->rowCount() > 0) {
                $_SESSION['success_message'] = 'รีวิวของคุณถูกแก้ไขเรียบร้อยแล้ว!';
            } else {
                $_SESSION['error_message'] = 'ไม่สามารถแก้ไขรีวิวได้ หรือคุณไม่มีสิทธิ์แก้ไขรีวิวนี้.';
            }
            break;

        case 'delete_review':
            // ต้องมี review_id และเป็นรีวิวของผู้ใช้ปัจจุบันเท่านั้น
            if ($review_id <= 0) {
                $_SESSION['error_message'] = 'ไม่พบ ID รีวิวที่ถูกต้องสำหรับการลบ.';
                break;
            }

            // ลบรีวิว
            $delete_sql = "
                DELETE FROM restaurantreview
                WHERE review_id = :review_id AND user_id = :user_id AND restaurant_id = :restaurant_id;
            ";
            $stmt_delete = $pdo->prepare($delete_sql);
            $stmt_delete->bindParam(':review_id', $review_id, PDO::PARAM_INT);
            $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_delete->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            if ($stmt_delete->rowCount() > 0) {
                $_SESSION['success_message'] = 'รีวิวของคุณถูกลบเรียบร้อยแล้ว!';
            } else {
                $_SESSION['error_message'] = 'ไม่สามารถลบรีวิวได้ หรือคุณไม่มีสิทธิ์ลบรีวิวนี้.';
            }
            break;

        default:
            $_SESSION['error_message'] = 'การดำเนินการไม่ถูกต้อง.';
            break;
    }

    // --- อัปเดต rating_avg และ rating_count เสมอหลังการดำเนินการ (เพิ่ม/แก้ไข/ลบ) ---
    updateRestaurantRating($pdo, $restaurant_id);

    $pdo->commit(); // ยืนยัน Transaction

} catch (\PDOException $e) {
    $pdo->rollBack(); // ยกเลิก Transaction หากมีข้อผิดพลาด
    $_SESSION['error_message'] = 'เกิดข้อผิดพลาดในการดำเนินการรีวิว: ' . $e->getMessage();
}

// Redirect กลับไปหน้ารายละเอียดร้าน
header('Location: restaurant_detail.php?id=' . $restaurant_id);
exit();
?>