<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดร้านอาหาร - อร่อยใกล้นนท์</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/restaurant_detail.css?v=<?php echo time(); ?>">
</head>
<body class="antialiased bg-background-soft text-primary">
    <?php
    session_start();
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        include('components/dynamic_header.php');
    } else {
        include('components/header.html');
    }
    ?>
    <div class="container max-w-6xl mx-auto my-8 p-4 md:p-6 lg:p-8">
        <?php

        require __DIR__ . '/../vendor/autoload.php';

        // โหลด dotenv
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); 
        $dotenv->load();

        // ดึงค่าจาก Environment Variables
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];
        $charset = $_ENV['DB_CHARSET'];

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            echo '<p class="error-message text-red-600 bg-red-100 p-4 rounded-md mb-4">ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage() . '</p>';
            exit();
        }

        // รับ restaurant_id จาก URL
        $restaurant_id = $_GET['id'] ?? null;

        if (!$restaurant_id || !is_numeric($restaurant_id)) {
            echo '<p class="error-message text-red-600 bg-red-100 p-4 rounded-md mb-4">ไม่พบ ID ร้านอาหารที่ถูกต้อง.</p>';
            echo '<a href="restaurants_list.php" class="back-button inline-block mt-8 bg-accent hover:bg-accent/80 text-text-light font-bold py-2 px-4 rounded transition duration-200 shadow-md">กลับไปหน้ารายการร้านอาหาร</a>';
            exit();
        }

        $current_day_of_week = date('w');
        if ($current_day_of_week == 0) {
            $current_day_of_week = 7;
        }
        $current_time = date('H:i:s');   

        // ดึงข้อมูลร้านอาหารหลัก ---
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

            // ดึงข้อมูลเวลาเปิด-ปิดทั้งหมดสำหรับทุกวัน ---
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

            // ประมวลผลเวลาเปิด-ปิดสำหรับสถานะปัจจุบัน
            $current_day_hours = null;
            foreach ($all_opening_hours as $oh) {
                if ($oh['day_of_week'] == $current_day_of_week) {
                    $current_day_hours = $oh;
                    break;
                }
            }

            if ($restaurant) {
                $images = [];
                if (!empty($restaurant['image_urls'])) {
                    $images = explode(',', $restaurant['image_urls']);
                    $images = array_map('trim', $images);
                }

                include 'process_opening_hours.php';
        ?>
        <div class="restaurant-detail">
            <div class="mb-8 p-6 sm:p-8 bg-white rounded-xl shadow-xl border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div class="flex-grow">
                        <h2 class="text-4xl lg:text-5xl font-extrabold text-[#4F2B14] border-b-4 border-secondary pb-2 relative inline-block group max-w-[700px] overflow-hidden line-clamp-2">
                            <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>
                            <span class="absolute bottom-0 left-0 w-full h-[4px] bg-accent-hover transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 ease-out"></span>
                            </h2>


                       <?php if (!empty($restaurant['food_types'])): ?>
                            <div class="mt-4 mb-4 flex flex-wrap items-center">
                                <?php
                                $food_types_array = explode(',', $restaurant['food_types']);
                                $food_types_array = array_map('trim', $food_types_array);

                                foreach ($food_types_array as $food_type):
                                    if (!empty($food_type)):
                                ?>
                                        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium mr-2 mb-2
                                            bg-[#4F2B14] text-[#F8C44E] hover:bg-opacity-80 transition-colors duration-200">
                                            <?php echo htmlspecialchars($food_type); ?>
                                        </span>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>

                        <p class="text-xl font-semibold flex items-center mb-4">
                            <span class="ml-2 flex items-center text-yellow-500 text-2xl">
                                <?php
                                $rating_avg = $restaurant['rating_avg'] ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($rating_avg >= $i) {
                                        echo '<span class="star-filled">&#9733;</span>'; // Filled star
                                    } elseif ($rating_avg > ($i - 1) && $rating_avg < $i) {
                                        if (fmod($rating_avg, 1) >= 0.5) {
                                            echo '<span class="star-half">&#9733;</span>'; // Unicode full star, but can be styled as half
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

                    <div class="sm:ml-auto">
                        <p class="text-xl font-semibold flex items-center">
                            <?php if (!empty($status_icon_path)): ?>
                                <img src="<?php echo htmlspecialchars($status_icon_path); ?>" alt="<?php echo htmlspecialchars($status); ?>" class="w-5 h-5 mr-2">
                            <?php endif; ?>
                            <span class="<?php echo $status_class; ?> text-base font-bold">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-8 p-6 sm:p-8 bg-white rounded-xl shadow-lg border border-gray-100">
                <?php if (!empty($images)): ?>
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-4 text-gray-700">รูปภาพ</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($images as $img_url): ?>
                                <img
                                    src="<?php echo htmlspecialchars($img_url); ?>"
                                    data-full-src="<?php echo htmlspecialchars($img_url); ?>"
                                    alt="รูปภาพ <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>"
                                    class="w-full h-36 object-cover rounded-lg shadow-md cursor-pointer transition-transform duration-200
                                        hover:scale-105 hover:shadow-xl
                                        ring-2 ring-transparent hover:ring-2 hover:ring-yellow-500"
                                />

                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="mb-6 p-6 sm:p-8 h-auto bg-white rounded-xl shadow-lg border border-gray-100 flex-1">
                    <h3 class="text-2xl font-bold mb-3 text-gray-700">ข้อมูลร้านอาหาร</h3>
                    <p class="font-bold text-lg mb-2">เวลาเปิดร้าน</p>
                    <?php if (!empty($formatted_opening_hours)): ?>
                        <?php foreach ($formatted_opening_hours as $line): ?>
                            <p class="text-gray-700 text-base leading-relaxed"><?php echo htmlspecialchars($line); ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600 text-base">ไม่มีข้อมูลเวลาเปิด-ปิด</p>
                    <?php endif; ?>

                    <br>

                    <p class="font-bold text-lg mb-2">ข้อมูลติดต่อ</p>

                    <p class="mb-2 text-gray-700 text-base">
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
                    <p class="mb-2 text-gray-700 text-base"><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($restaurant['phone'] ?? 'ไม่ระบุ'); ?></p>
                    <?php if (!empty($restaurant['website'] ?? '')): ?>
                        <p class="mb-2 text-gray-700 text-base"><strong>เว็บไซต์:</strong> <a href="<?php echo htmlspecialchars($restaurant['website']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($restaurant['website']); ?></a></p>
                    <?php endif; ?>
                </div>
                <div class="mb-6 p-6 sm:p-8 h-auto bg-white rounded-xl shadow-lg border border-gray-100 flex-1">
                    <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                        <div class="map-section w-full">
                            <h3 class="text-2xl font-bold mb-3 text-gray-700">แผนที่</h3>
                            <div class="aspect-w-16 aspect-h-9 md:aspect-w-1 md:aspect-h-1 md:h-[200px] w-full">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    frameborder="0" style="border:0"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox=<?php

                                        $lon_offset = 0.01;
                                        $lat_offset = 0.01;

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
                            </div>
                            <p class="text-sm text-gray-500 mt-2">แผนที่จาก OpenStreetMap</p>
                            <p class="mt-4">
                                <a href="https://www.openstreetmap.org/?mlat=<?php echo htmlspecialchars($restaurant['latitude']); ?>&mlon=<?php echo htmlspecialchars($restaurant['longitude']); ?>#map=17/<?php echo htmlspecialchars($restaurant['latitude']); ?>/<?php htmlspecialchars($restaurant['longitude']); ?>"
                                target="_blank"
                                class="map-link inline-block px-5 py-2 rounded-full font-bold text-base shadow-lg transform transition-all duration-300
                                        bg-[#4F2B14] text-[#F8C44E]
                                        hover:scale-105 hover:shadow-xl hover:bg-[#6A3F1F]
                                        focus:outline-none focus:ring-4 focus:ring-[#4F2B14] focus:ring-opacity-50
                                        active:scale-95 active:bg-[#3B200F]">
                                    ดูแผนที่ขนาดใหญ่บน OpenStreetMap
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-10 border-gray-300">

            <div class="reviews-section">
                <h3 class="text-3xl font-bold mb-6 text-gray-800">ความคิดเห็นและรีวิว</h3>

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
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <div class="add-review-form mt-6 p-6 bg-white rounded-xl shadow-lg border border-gray-100">
                    <h4 class="text-2xl font-bold mb-4 text-gray-800">
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
                                <label class="block text-gray-700 text-base font-bold mb-2">คะแนน (1-5 ดาว):</label>
                                <div id="star-rating" class="star-rating">
                                    <span data-value="5" class="star">&#9733;</span>
                                    <span data-value="4" class="star">&#9733;</span>
                                    <span data-value="3" class="star">&#9733;</span>
                                    <span data-value="2" class="star">&#9733;</span>
                                    <span data-value="1" class="star">&#9733;</span>
                                </div>
                                <input type="hidden" name="rating" id="rating" value="<?php echo $user_has_reviewed ? $user_review['rating'] : ''; ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="review_text" class="block text-gray-700 text-base font-bold mb-2">ความคิดเห็นของคุณ:</label>
                                <textarea name="review_text" id="review_text" rows="5" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-all duration-200" placeholder="เขียนความคิดเห็นของคุณที่นี่..." required><?php echo ($user_has_reviewed) ? htmlspecialchars($user_review['text']) : ''; ?></textarea>
                            </div>
                            <button type="submit"
                                    class="bg-[#4F2B14] text-[#F8C44E] font-bold py-2 px-4 rounded-md
                                        focus:outline-none focus:ring-2 focus:ring-[#4F2B14] focus:ring-offset-2 focus:ring-opacity-75
                                        transform transition-all duration-300 shadow-md
                                        hover:scale-105 hover:shadow-lg hover:bg-[#6A3F1F] active:scale-95 active:bg-[#3B200F]">
                                <?php echo ($user_has_reviewed) ? 'บันทึกการแก้ไข' : 'ส่งรีวิว'; ?>
                            </button>
                        </form>
                        <?php if ($user_has_reviewed): ?>
                            <form action="submit_review.php" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่ต้องการลบรีวิวนี้?');" class="mt-3">
                                <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant_id); ?>">
                                <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($user_review['review_id']); ?>">
                                <input type="hidden" name="action" value="delete_review">
                                <button type="submit"
                                        class="bg-red-600 text-white font-bold py-2 px-4 rounded-md
                                            focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-opacity-75
                                            transform transition-all duration-300 shadow-md
                                            hover:scale-105 hover:shadow-lg hover:bg-red-700 active:scale-95 active:bg-red-800">
                                    ลบรีวิว
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="mt-4 text-gray-600 text-base">
                            <a href="login.php" class="text-[#4F2B14] text-xl hover:underline font-bold">เข้าสู่ระบบ</a> เพื่อเขียนรีวิว
                        </p>
                    <?php endif; ?>
                </div>

                <h4 class="text-2xl font-bold mb-4 mt-8 text-gray-700">รีวิวทั้งหมด</h4>
                <div class="all-reviews-list">
                <?php
                // ดึงข้อมูลรีวิวทั้งหมด
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
                            <div class="review-item border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 flex items-start bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                                <div class="flex-shrink-0 mr-4">
                                    <img src="<?php echo htmlspecialchars($review['profile_picture_url'] ?? '../images/default_profile.png'); ?>"
                                        alt="รูปโปรไฟล์" class="w-14 h-14 rounded-full object-cover border-2 border-gray-200 shadow-sm">
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
                                    <p class="mt-2 text-gray-800 leading-relaxed">
                                        <?php echo nl2br(htmlspecialchars($review['text'])); ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-gray-600 mt-4 text-base">ยังไม่มีความคิดเห็นสำหรับร้านนี้.</p>';
                    }
                } catch (\PDOException $e) {
                    echo '<p class="error-message text-red-600 bg-red-100 p-4 rounded-md mt-4">เกิดข้อผิดพลาดในการดึงความคิดเห็น: ' . $e->getMessage() . '</p>';
                }
                ?>
                </div>
            </div>
        </div>
        <?php
            } else { 
                echo '<p class="error-message text-red-600 bg-red-100 p-4 rounded-md mb-4">ไม่พบข้อมูลร้านอาหาร.</p>';
            }

        } catch (\PDOException $e) { 
            echo '<p class="error-message text-red-600 bg-red-100 p-4 rounded-md mb-4">เกิดข้อผิดพลาดในการดึงข้อมูลร้านอาหาร: ' . $e->getMessage() . '</p>';
        }
        ?>
        <br>
        <a href="restaurants_list.php" 
        class="inline-block px-5 py-2 rounded-full font-bold text-base shadow-lg transform transition-all duration-300
                                        bg-[#4F2B14] text-[#F8C44E]
                                        hover:scale-105 hover:shadow-xl hover:bg-[#6A3F1F]
                                        focus:outline-none focus:ring-4 focus:ring-[#4F2B14] focus:ring-opacity-50
                                        active:scale-95 active:bg-[#3B200F]">
            กลับไปหน้ารายการร้านอาหาร
        </a>
    </div>


    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/restaurant_detail.js?v=<?php echo time(); ?>"></script>
</body>
<?php include('components/footer.html'); ?>
</html>