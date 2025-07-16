<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดร้านอาหาร - อร่อยใกล้นนท์</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @tailwind base;
        @tailwind components;
        @tailwind utilities;
    </style>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/restaurant_detail.css?v=<?php echo time(); ?>">
</head>
<body class="antialiased text-gray-800">
    <?php
    session_start(); // ต้องอยู่ก่อน include ใดๆ ที่จะใช้ $_SESSION
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        include('components/dynamic_header.php');
    } else {
        include('components/header.html');
    }
    ?>
    <div class="container max-w-4xl mx-auto my-8 p-6 bg-white rounded-lg shadow-xl">
        <?php
        // --- ตั้งค่าการเชื่อมต่อฐานข้อมูล (เหมือนกับ get_restaurants.php) ---
        $host = 'localhost';
        $db = 'eat_near_non';
        $user = 'root';
        $pass = 'ppgdmild';
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
            echo '<p class="error-message">ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage() . '</p>';
            exit();
        }

        // --- รับ restaurant_id จาก URL ---
        $restaurant_id = $_GET['id'] ?? null;

        if (!$restaurant_id || !is_numeric($restaurant_id)) {
            echo '<p class="error-message">ไม่พบ ID ร้านอาหารที่ถูกต้อง.</p>';
            echo '<a href="restaurants_list.php" class="back-button">กลับไปหน้ารายการร้านอาหาร</a>';
            exit();
        }

        // --- คำนวณวันและเวลาปัจจุบันสำหรับสถานะเปิด/ปิด (เหมือนเดิม) ---
        $current_day_of_week = date('w'); // 0 (อาทิตย์) ถึง 6 (เสาร์)
        $current_time = date('H:i:s');    // เวลาปัจจุบัน เช่น "14:30:00"

        // --- ดึงข้อมูลร้านอาหารหลัก ---
        $sql = "
            SELECT
                r.restaurant_id,
                r.restaurant_name,
                r.address_line1,
                r.address_line2,
                r.sub_district,
                r.district,
                r.province,
                r.phone,
                r.latitude,
                r.longitude,
                r.rating_avg,
                r.rating_count,
                GROUP_CONCAT(DISTINCT ft.food_type_name SEPARATOR ', ') AS food_types,
                (
                    SELECT GROUP_CONCAT(ri.image_url ORDER BY ri.is_primary DESC, ri.image_id ASC)
                    FROM restaurantimage AS ri
                    WHERE ri.restaurant_id = r.restaurant_id
                ) AS image_urls
            FROM
                restaurant AS r
            LEFT JOIN
                restaurantfoodtype AS rft ON r.restaurant_id = rft.restaurant_id
            LEFT JOIN
                foodtype AS ft ON rft.food_type_id = ft.food_type_id
            WHERE
                r.restaurant_id = :restaurant_id
            GROUP BY
                r.restaurant_id,
                r.restaurant_name,
                r.address_line1,
                r.address_line2,
                r.sub_district,
                r.district,
                r.province,
                r.phone,
                r.latitude, r.longitude, r.rating_avg, r.rating_count;
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt->execute();
            $restaurant = $stmt->fetch();

            // --- ดึงข้อมูลเวลาเปิด-ปิดทั้งหมดสำหรับทุกวัน ---
            $opening_hours_sql = "
                SELECT day_of_week, open_time, close_time, is_closed
                FROM restaurant_opening_hours
                WHERE restaurant_id = :restaurant_id
                ORDER BY day_of_week ASC;
            ";
            $stmt_hours = $pdo->prepare($opening_hours_sql);
            $stmt_hours->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt_hours->execute();
            $all_opening_hours = $stmt_hours->fetchAll();

            // --- ประมวลผลเวลาเปิด-ปิดสำหรับสถานะปัจจุบัน ---
            $current_day_hours = null;
            foreach ($all_opening_hours as $oh) {
                if ($oh['day_of_week'] == $current_day_of_week) {
                    $current_day_hours = $oh;
                    break;
                }
            }

            if ($restaurant) {
                // --- ประมวลผล URL รูปภาพ ---
                $images = [];
                if (!empty($restaurant['image_urls'])) {
                    $images = explode(',', $restaurant['image_urls']);
                    $images = array_map('trim', $images);
                }

                // --- คำนวณสถานะเปิด/ปิด (ใช้ $current_day_hours ที่ดึงมา) ---
                $status = 'ไม่ทราบสถานะ';
                $status_class = 'unknown';

                if ($current_day_hours && $current_day_hours['is_closed'] == 1) {
                    $status = 'ปิดถาวรในวันนี้';
                    $status_class = 'closed';
                } else if ($current_day_hours && (empty($current_day_hours['open_time']) || empty($current_day_hours['close_time']))) {
                    $status = 'ไม่ระบุเวลาทำการในวันนี้';
                    $status_class = 'unknown';
                } else if ($current_day_hours) {
                    if ($current_time >= $current_day_hours['open_time'] && $current_time <= $current_day_hours['close_time']) {
                        $status = 'เปิดอยู่';
                        $status_class = 'open';
                    } else {
                        $status = 'ปิด (นอกเวลาทำการ)';
                        $status_class = 'closed';
                    }
                } else {
                    $status = 'ไม่มีข้อมูลเวลาทำการสำหรับวันนี้';
                    $status_class = 'unknown';
                }

                // --- จัดกลุ่มเวลาเปิด-ปิดสำหรับแสดงผลทั้งหมด (หลังแก้ไขฐานข้อมูล day_of_week: 0 เป็น 7) ---
                $formatted_opening_hours = [];
                // เปลี่ยน day_names ให้วันอาทิตย์อยู่ท้ายสุด เพราะใน DB เป็นเลข 7
                $day_names = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
                // หมายเหตุ: Index จะเปลี่ยนไป 0=จันทร์, ..., 5=เสาร์, 6=อาทิตย์

                // สร้าง array ที่มีข้อมูลเวลาทำการของทุกวัน
                $daily_hours_map = [];
                // ปรับลูปให้ครอบคลุม 1-7
                for ($i = 1; $i <= 7; $i++) {
                    $daily_hours_map[$i] = ['open_time' => null, 'close_time' => null, 'is_closed' => 0];
                }
                foreach ($all_opening_hours as $oh) {
                    // ตรวจสอบค่า day_of_week ก่อนใส่ใน map
                    $day_key = ($oh['day_of_week'] == 0) ? 7 : $oh['day_of_week']; // ยังคงเผื่อกรณีที่ข้อมูลเก่า 0 อาจจะยังหลงเหลือ
                    $daily_hours_map[$day_key] = [
                        'open_time' => $oh['open_time'],
                        'close_time' => $oh['close_time'],
                        'is_closed' => $oh['is_closed']
                    ];
                }
                ksort($daily_hours_map); // เรียงตามวันของสัปดาห์ 1-7

                $grouped_hours = []; // ใช้เก็บเวลาเป็นคีย์และ array ของวันเป็นค่า

                foreach ($daily_hours_map as $day_of_week => $times) {
                    // ข้ามวันปิดถาวร
                    if ($times['is_closed'] == 1) {
                        continue; // ข้ามวันปิดไปเลย
                    }

                    $time_str = '';
                    if (empty($times['open_time']) || empty($times['close_time'])) {
                        $time_str = 'ไม่ระบุเวลาทำการ';
                    } else {
                        $time_str = substr($times['open_time'], 0, 5) . ' - ' . substr($times['close_time'], 0, 5);
                    }

                    // เพิ่มวันเข้าไปในกลุ่มของเวลานั้นๆ
                    if (!isset($grouped_hours[$time_str])) {
                        $grouped_hours[$time_str] = [];
                    }
                    $grouped_hours[$time_str][] = $day_of_week;
                }

                // แปลงข้อมูลที่จัดกลุ่มแล้วให้เป็นข้อความที่แสดงผล
                $final_display_hours = [];
                foreach ($grouped_hours as $time => $days) {
                    sort($days); // เรียงวันในกลุ่มให้ถูกต้อง (เช่น [1,2,3,6,7] )

                    $day_parts = [];
                    $current_sequence = [];

                    foreach ($days as $day_index_db) {
                        // ปรับ index ให้ตรงกับ $day_names array (0-based)
                        $day_index_for_name = $day_index_db - 1;

                        if (empty($current_sequence) || $day_index_db === end($current_sequence) + 1) {
                            // วันต่อเนื่องกัน หรือเริ่มกลุ่มใหม่
                            $current_sequence[] = $day_index_db;
                        } else {
                            // วันไม่ต่อเนื่องกัน, จบกลุ่มปัจจุบันและเริ่มกลุ่มใหม่
                            if (count($current_sequence) === 1) {
                                $day_parts[] = $day_names[$current_sequence[0] - 1];
                            } else {
                                $start_day_name = $day_names[$current_sequence[0] - 1];
                                $end_day_name = $day_names[end($current_sequence) - 1];
                                $day_parts[] = $start_day_name . ' - ' . $end_day_name;
                            }
                            $current_sequence = [$day_index_db]; // เริ่ม segment ใหม่
                        }
                    }

                    // เพิ่ม segment สุดท้าย
                    if (!empty($current_sequence)) {
                        if (count($current_sequence) === 1) {
                            $day_parts[] = $day_names[$current_sequence[0] - 1];
                        } else {
                            $start_day_name = $day_names[$current_sequence[0] - 1];
                            $end_day_name = $day_names[end($current_sequence) - 1];
                            $day_parts[] = $start_day_name . ' - ' . $end_day_name;
                        }
                    }
                    $final_display_hours[] = implode(', ', $day_parts) . ' ' . $time;
                }

                // เรียงลำดับการแสดงผลของเวลาทำการตามวันที่เริ่มต้นของช่วงเวลา (ถ้าต้องการ)
                // หรืออาจจะปล่อยให้เรียงตามที่ grouped_hours เจอเป็นครั้งแรก
                // สำหรับการแสดงผลที่ถูกต้องตามช่วงวัน ผมแนะนำให้เรียงตามลำดับวันแรกของแต่ละช่วง
                // แต่สำหรับความเรียบง่ายตามที่ถามมาข้างต้น ผมใช้ $final_display_hours โดยตรง

                $formatted_opening_hours = $final_display_hours; // กำหนดค่าให้ตัวแปรเดิม
                // --- จบส่วนแก้ไข ---
                ?>
                <div class="restaurant-detail">
                    <div class="mb-6">
                        <h2 class="text-4xl font-extrabold text-[#4F2B14] mb-2 leading-tight">
                            <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>
                        </h2>
                        <?php if (!empty($restaurant['food_types'])): ?>
                            <p class="text-gray-600 text-lg">
                                <strong>ประเภทอาหาร:</strong> <?php echo htmlspecialchars($restaurant['food_types']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($images)): ?>
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold mb-4 text-gray-700">รูปภาพ</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php foreach ($images as $img_url): ?>
                                    <img src="<?php echo htmlspecialchars($img_url); ?>"
                                         alt="รูปภาพ <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>"
                                         class="w-full h-36 object-cover rounded-lg shadow-md cursor-pointer hover:scale-105 transition-transform duration-200"
                                         data-full-src="<?php echo htmlspecialchars($img_url); ?>">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <p class="text-xl font-semibold mb-2 sm:mb-0">
                            <strong>สถานะ:</strong>
                            <span class="status <?php echo $status_class; ?> text-base px-3 py-1 rounded-md">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </p>
                        <p class="text-xl font-semibold flex items-center">
                            <strong>คะแนน:</strong>
                            <span class="ml-2 flex items-center text-yellow-500 text-2xl">
                                <?php
                                $rating_avg = $restaurant['rating_avg'] ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($rating_avg >= $i) {
                                        echo '<span class="star-filled">&#9733;</span>'; // Filled star
                                    } elseif ($rating_avg > ($i - 1) && $rating_avg < $i) {
                                        // Simple half-star logic: if rating is like 3.5-3.9, show half star.
                                        // For more precise half stars, you might need SVG icons or font awesome.
                                        if (fmod($rating_avg, 1) >= 0.5) {
                                            echo '<span class="star-half">&#9733;</span>'; // Unicode full star, but we can style it as half with CSS if needed, or use a proper half star character.
                                        } else {
                                            echo '<span class="star-empty">&#9734;</span>'; // Empty star
                                        }
                                    } else {
                                        echo '<span class="star-empty">&#9734;</span>'; // Empty star
                                    }
                                }
                                ?>
                            </span>
                            <span class="ml-2 text-gray-700 text-lg">
                                <?php echo htmlspecialchars(number_format($rating_avg, 1)); ?>
                                (<?php echo htmlspecialchars($restaurant['rating_count'] ?? 0); ?> รีวิว)
                            </span>
                        </p>
                    </div>

                    <div class="mb-6 p-5 bg-gray-50 rounded-lg shadow-inner">
                        <h3 class="text-2xl font-bold mb-3 text-gray-700">เวลาเปิด-ปิดร้าน</h3>
                        <?php if (!empty($formatted_opening_hours)): ?>
                            <ul class="list-disc list-inside ml-4 text-gray-700">
                                <?php foreach ($formatted_opening_hours as $line): ?>
                                    <li><?php echo htmlspecialchars($line); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-600">ไม่มีข้อมูลเวลาเปิด-ปิด</p>
                        <?php endif; ?>
                    </div>

                    <hr class="my-8 border-gray-300">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-700">ข้อมูลติดต่อ</h3>
                            <p class="mb-2 text-gray-700">
                                <strong>ที่อยู่:</strong>
                                <?php
                                $full_address = [];
                                if (!empty($restaurant['address_line1'])) $full_address[] = $restaurant['address_line1'];
                                if (!empty($restaurant['address_line2'])) $full_address[] = $restaurant['address_line2'];
                                if (!empty($restaurant['sub_district'])) $full_address[] = $restaurant['sub_district'];
                                if (!empty($restaurant['district'])) $full_address[] = $restaurant['district'];
                                if (!empty($restaurant['province'])) $full_address[] = $restaurant['province'];
                                echo htmlspecialchars(implode(', ', $full_address) ?: 'ไม่ระบุ');
                                ?>
                            </p>
                            <p class="mb-2 text-gray-700"><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($restaurant['phone'] ?? 'ไม่ระบุ'); ?></p>
                            <?php if (!empty($restaurant['website'] ?? '')): ?>
                                <p class="mb-2 text-gray-700"><strong>เว็บไซต์:</strong> <a href="<?php echo htmlspecialchars($restaurant['website']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($restaurant['website']); ?></a></p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                            <div class="map-section w-full aspect-w-16 aspect-h-9 md:aspect-w-1 md:aspect-h-1">
                                <h3 class="text-2xl font-bold mb-3 text-gray-700">แผนที่</h3>
                                <iframe
                                    width="100%"
                                    height="100%"
                                    frameborder="0" style="border:0"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox=<?php
                                        // Define a larger offset for the bounding box to ensure visibility.
                                        // These values (0.01) usually provide a good initial view for a single marker.
                                        $lon_offset = 0.01;
                                        $lat_offset = 0.01;

                                        // Calculate the bounding box
                                        $min_lon = $restaurant['longitude'] - $lon_offset;
                                        $min_lat = $restaurant['latitude'] - $lat_offset;
                                        $max_lon = $restaurant['longitude'] + $lon_offset;
                                        $max_lat = $restaurant['latitude'] + $lat_offset;

                                        echo htmlspecialchars($min_lon) . '%2C' .
                                            htmlspecialchars($min_lat) . '%2C' .
                                            htmlspecialchars($max_lon) . '%2C' .
                                            htmlspecialchars($max_lat);
                                    ?>&layer=mapnik&marker=<?php
                                        echo htmlspecialchars($restaurant['latitude']) . '%2C' .
                                            htmlspecialchars($restaurant['longitude']);
                                    ?>"
                                    allowfullscreen=""
                                    aria-hidden="false"
                                    tabindex="0"
                                    class="rounded-lg shadow-md">
                                </iframe>
                                <p class="text-sm text-gray-500 mt-2">แผนที่จาก OpenStreetMap</p>
                                <p class="mt-2">
                                    <a href="https://www.openstreetmap.org/?mlat=<?php echo htmlspecialchars($restaurant['latitude']); ?>&mlon=<?php echo htmlspecialchars($restaurant['longitude']); ?>#map=17/<?php echo htmlspecialchars($restaurant['latitude']); ?>/<?php echo htmlspecialchars($restaurant['longitude']); ?>" target="_blank" class="map-link inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition duration-200">
                                        ดูแผนที่ขนาดใหญ่บน OpenStreetMap
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div><br><br><br>

                    <hr class="my-8 border-gray-300">

                    <div class="reviews-section">
                        <h3 class="text-2xl font-bold mb-4 text-gray-700">ความคิดเห็นและรีวิว</h3>

                        <?php
                        // ดึงข้อมูลรีวิวของผู้ใช้ปัจจุบัน (ถ้ามี)
                        $user_has_reviewed = false;
                        $user_review = null;
                        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                            $current_user_id = $_SESSION['user_id'];
                            $check_user_review_sql = "
                                SELECT
                                    rr.review_id,
                                    rr.rating,
                                    rr.review_date,
                                    rr.text,
                                    au.firstname,
                                    au.surname,
                                    au.profile_picture_url -- Added profile_picture_url
                                FROM
                                    restaurantreview AS rr
                                JOIN
                                    appuser AS au ON rr.user_id = au.user_id
                                WHERE
                                    rr.restaurant_id = :restaurant_id AND rr.user_id = :user_id;
                            ";
                            $stmt_check_user_review = $pdo->prepare($check_user_review_sql);
                            $stmt_check_user_review->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
                            $stmt_check_user_review->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
                            $stmt_check_user_review->execute();
                            $user_review = $stmt_check_user_review->fetch();
                            if ($user_review) {
                                $user_has_reviewed = true;
                            }
                        }

                        // แสดงข้อความ Success/Error จาก Session
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                            unset($_SESSION['success_message']);
                        }
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                            unset($_SESSION['error_message']);
                        }
                        ?>

                        <div class="add-review-form mt-4 p-6 bg-gray-50 rounded-lg shadow-md border border-gray-200">
                            <h4 class="text-xl font-bold mb-4 text-gray-800">
                                <?php echo ($user_has_reviewed) ? 'แก้ไขรีวิวของคุณ' : 'เขียนรีวิวของคุณ'; ?>
                            </h4>
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <form action="submit_review.php" method="POST">
                                    <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant_id); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                                    <?php if ($user_has_reviewed): ?>
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($user_review['review_id']); ?>">
                                        <input type="hidden" name="action" value="update_review">
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="add_review">
                                    <?php endif; ?>

                                    <div class="mb-4">
                                        <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">คะแนน (1-5 ดาว):</label>
                                        <select name="rating" id="rating" class="shadow border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            <option value="">เลือกคะแนน</option>
                                            <option value="5" <?php echo ($user_has_reviewed && $user_review['rating'] == 5) ? 'selected' : ''; ?>>5 ดาว - ยอดเยี่ยม</option>
                                            <option value="4" <?php echo ($user_has_reviewed && $user_review['rating'] == 4) ? 'selected' : ''; ?>>4 ดาว - ดีมาก</option>
                                            <option value="3" <?php echo ($user_has_reviewed && $user_review['rating'] == 3) ? 'selected' : ''; ?>>3 ดาว - ปานกลาง</option>
                                            <option value="2" <?php echo ($user_has_reviewed && $user_review['rating'] == 2) ? 'selected' : ''; ?>>2 ดาว - ไม่ค่อยดี</option>
                                            <option value="1" <?php echo ($user_has_reviewed && $user_review['rating'] == 1) ? 'selected' : ''; ?>>1 ดาว - แย่มาก</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="review_text" class="block text-gray-700 text-sm font-bold mb-2">ความคิดเห็นของคุณ:</label>
                                        <textarea name="review_text" id="review_text" rows="5" class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="เขียนความคิดเห็นของคุณที่นี่..." required><?php echo ($user_has_reviewed) ? htmlspecialchars($user_review['text']) : ''; ?></textarea>
                                    </div>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <?php echo ($user_has_reviewed) ? 'บันทึกการแก้ไข' : 'ส่งรีวิว'; ?>
                                    </button>
                                </form>
                                <?php if ($user_has_reviewed): ?>
                                    <form action="submit_review.php" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่ต้องการลบรีวิวนี้?');" class="mt-2">
                                        <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant_id); ?>">
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($user_review['review_id']); ?>">
                                        <input type="hidden" name="action" value="delete_review">
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            ลบรีวิว
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="mt-4 text-gray-600">
                                    <a href="login.php" class="text-blue-600 hover:underline font-semibold">เข้าสู่ระบบ</a> เพื่อเขียนรีวิว
                                </p>
                            <?php endif; ?>
                        </div>

                        <h4 class="text-xl font-bold mb-4 mt-8 text-gray-700">รีวิวทั้งหมด</h4>
                        <div class="all-reviews-list">
                        <?php
                        // ดึงข้อมูลรีวิวทั้งหมด ยกเว้นรีวิวของผู้ใช้ปัจจุบันที่เพิ่งถูกแสดงในฟอร์ม (ถ้ามี)
                        $reviews_sql = "
                            SELECT
                                rr.review_id,
                                rr.rating,
                                rr.review_date,
                                rr.text,
                                au.firstname,
                                au.surname,
                                au.profile_picture_url
                            FROM
                                restaurantreview AS rr
                            JOIN
                                appuser AS au ON rr.user_id = au.user_id
                            WHERE
                                rr.restaurant_id = :restaurant_id
                        ";
                        $reviews_sql .= " ORDER BY rr.review_date DESC;";

                        try {
                            $stmt_reviews = $pdo->prepare($reviews_sql);
                            $stmt_reviews->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
                            $stmt_reviews->execute();
                            $reviews = $stmt_reviews->fetchAll();

                            if (!empty($reviews)) {
                                foreach ($reviews as $review) {
                                    ?>
                                    <div class="review-item border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 flex items-start bg-white p-4 rounded-lg shadow-sm">
                                        <div class="flex-shrink-0 mr-4">
                                            <img src="<?php echo htmlspecialchars($review['profile_picture_url'] ?? '../images/default_profile.png'); ?>"
                                                 alt="รูปโปรไฟล์" class="w-12 h-12 rounded-full object-cover border border-gray-200">
                                        </div>
                                        <div class="flex-grow">
                                            <p class="text-lg font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($review['firstname'] . ' ' . $review['surname']); ?>
                                            </p>
                                            <p class="flex items-center text-yellow-500 text-xl mt-1">
                                                <?php
                                                $review_rating = $review['rating'];
                                                for ($i = 0; $i < 5; $i++) {
                                                    if ($i < $review_rating) {
                                                        echo '&#9733;'; // Filled star
                                                    } else {
                                                        echo '&#9734;'; // Empty star
                                                    }
                                                }
                                                ?>
                                                <span class="ml-2 text-gray-600 text-sm">
                                                    เมื่อ: <?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?>
                                                </span>
                                            </p>
                                            <p class="mt-2 text-gray-800">
                                                <?php echo nl2br(htmlspecialchars($review['text'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<p class="text-gray-600">ยังไม่มีความคิดเห็นสำหรับร้านนี้.</p>';
                            }
                        } catch (\PDOException $e) {
                            echo '<p class="error-message">เกิดข้อผิดพลาดในการดึงความคิดเห็น: ' . $e->getMessage() . '</p>';
                        }
                        ?>
                        </div>
                    </div>
                </div>
            <?php
            } else {
                echo '<p class="error-message">ไม่พบข้อมูลร้านอาหารนี้.</p>';
            }
        } catch (\PDOException $e) {
            echo '<p class="error-message">เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage() . '</p>';
        }
        ?>
        <a href="restaurants_list.php" class="back-button mt-8 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200">
            กลับไปหน้ารายการร้านอาหาร
        </a>
    </div>

    <div id="imageModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>



    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/restaurant_detail.js?v=<?php echo time(); ?>"></script>
</body>
<?php include('components/footer.html'); ?>
</html>