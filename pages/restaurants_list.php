<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการร้านอาหาร - อร่อยใกล้นนท์</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/restaurant_list.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    session_start();
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        include('components/dynamic_header.php');
    } else {
        include('components/header.html');
    }
    ?>

    <div class="container">
        <h1 class="Title">ร้านอาหารในจังหวัดนนทบุรี</h1>

        <div class="filter-section">
            <div class="filter-center-container">

                <!-- Search Box -->
                <div class="filter-group search">
                    <div class="search-container">
                        <img src="../static/images/icon/search.png" alt="Search Icon" class="search-icon">
                        <input type="text" id="restaurant-name-search" placeholder="พิมพ์ชื่อร้านอาหาร...">
                    </div>
                </div>

                <!-- District Filter -->
                <div class="filter-group dropdown">
                    <div class="dropdown">
                        <button class="dropbtn" id="district-dropdown-btn">
                            เลือกอำเภอ <span class="arrow">▼</span>
                        </button>
                        <div class="dropdown-content" id="district-filter-checkboxes">
                            <input type="checkbox" id="district1" name="district[]" value=""><label for="district1"> ทั้งหมด</label>
                            <input type="checkbox" id="district2" name="district[]" value="เมืองนนทบุรี"><label for="district2"> เมืองนนทบุรี</label>
                            <input type="checkbox" id="district3" name="district[]" value="ปากเกร็ด"><label for="district3"> ปากเกร็ด</label>
                            <input type="checkbox" id="district4" name="district[]" value="บางใหญ่"><label for="district4"> บางใหญ่</label>
                            <input type="checkbox" id="district5" name="district[]" value="บางบัวทอง"><label for="district5"> บางบัวทอง</label>
                            <input type="checkbox" id="district6" name="district[]" value="บางกรวย"><label for="district6"> บางกรวย</label>
                            <input type="checkbox" id="district7" name="district[]" value="ไทรน้อย"><label for="district7"> ไทรน้อย</label>
                        </div>
                    </div>
                </div>

                <!-- Food Type Filter -->
                <div class="filter-group dropdown">
                    <div class="dropdown">
                        <button class="dropbtn" id="food-type-dropdown-btn">
                            เลือกประเภทร้านอาหาร <span class="arrow">▼</span>
                        </button>
                        <div class="dropdown-content" id="food-type-filter-checkboxes">
                            <input type="checkbox" id="foodtype1" name="food_type[]" value=""><label for="foodtype1"> ทั้งหมด</label>
                            <input type="checkbox" id="foodtype2" name="food_type[]" value="อาหารจานเดียว"><label for="foodtype2"> อาหารจานเดียว</label>
                            <input type="checkbox" id="foodtype3" name="food_type[]" value="อาหารตามสั่ง"><label for="foodtype3"> อาหารตามสั่ง</label>
                            <input type="checkbox" id="foodtype4" name="food_type[]" value="ก๋วยเตี๋ยว"><label for="foodtype4"> ก๋วยเตี๋ยว</label>
                            <input type="checkbox" id="foodtype5" name="food_type[]" value="อาหารอีสาน"><label for="foodtype5"> อาหารอีสาน</label>
                            <input type="checkbox" id="foodtype6" name="food_type[]" value="อาหารทะเล"><label for="foodtype6"> อาหารทะเล</label>
                            <input type="checkbox" id="foodtype7" name="food_type[]" value="อาหารญี่ปุ่น"><label for="foodtype7"> อาหารญี่ปุ่น</label>
                            <input type="checkbox" id="foodtype8" name="food_type[]" value="อาหารตะวันตก"><label for="foodtype8"> อาหารตะวันตก</label>
                            <input type="checkbox" id="foodtype9" name="food_type[]" value="อาหารปักษ์ใต้"><label for="foodtype9"> อาหารปักษ์ใต้</label>
                            <input type="checkbox" id="foodtype10" name="food_type[]" value="อาหารจีน"><label for="foodtype10"> อาหารจีน</label>
                            <input type="checkbox" id="foodtype11" name="food_type[]" value="ของหวาน"><label for="foodtype11"> ของหวาน</label>
                            <input type="checkbox" id="foodtype12" name="food_type[]" value="อาหารริมทาง"><label for="foodtype12"> อาหารริมทาง</label>
                            <input type="checkbox" id="foodtype13" name="food_type[]" value="เครื่องดื่ม"><label for="foodtype13"> เครื่องดื่ม</label>
                            <input type="checkbox" id="foodtype14" name="food_type[]" value="อื่นๆ"><label for="foodtype14"> อื่นๆ</label>
                        </div>
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="filter-group dropdown">
                    <div class="dropdown relative inline-block text-left">
                        <button id="sort-dropdown-btn" class="dropbtn px-4 py-2 bg-secondary text-white rounded-md">
                            เรียงตาม <span class="arrow">▼</span>
                        </button>
                        <div class="dropdown-content absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" id="sort-options">
                            <a href="#" data-sort="default" class="sort-option active block px-4 py-2 text-gray-700 hover:bg-gray-100">เริ่มต้น</a>
                            <a href="#" data-sort="rating_desc" class="sort-option block px-4 py-2 text-gray-700 hover:bg-gray-100">ความนิยม (มากไปน้อย)</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Restaurant List -->
        <div id="restaurant-list" class="restaurant-list">
            <p class="no-results">กำลังโหลดข้อมูลร้านอาหาร...</p>
        </div>
    </div>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/restaurants_list.js?v=<?php echo time(); ?>"></script>
    <script>

    </script>

</body>
</html>
