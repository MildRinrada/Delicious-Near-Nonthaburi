<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการร้านอาหาร - อร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/restaurant_list.css?v=<?php echo time(); ?>">

</head>
<body>
    <?php
    session_start(); // ต้องอยู่ก่อน include ใดๆ ที่จะใช้ $_SESSION
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        include('components/dynamic_header.php');
    } else {
        include('components/header.html');
    }
    ?>

    <div class="container">
        <h1 class="Title">ร้านอาหารในจังหวัดนนทบุรี</h1>

        <div class="filter-section">
            <div class="filter-group">
                <label for="district-filter">อำเภอ:</label>
                <select id="district-filter">
                    <option value="">ทั้งหมด</option>
                    <option value="เมืองนนทบุรี">เมืองนนทบุรี</option>
                    <option value="ปากเกร็ด">ปากเกร็ด</option>
                    <option value="บางใหญ่">บางใหญ่</option>
                    <option value="บางบัวทอง">บางบัวทอง</option>
                    <option value="บางกรวย">บางกรวย</option>
                    <option value="ไทรน้อย">ไทรน้อย</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="food-type-filter">ประเภทอาหาร:</label>
                <select id="food-type-filter">
                    <option value="">ทั้งหมด</option>
                    <option value="อาหารจานเดียว">อาหารจานเดียว</option>
                    <option value="อาหารตามสั่ง">อาหารตามสั่ง</option>
                    <option value="ก๋วยเตี๋ยว">ก๋วยเตี๋ยว</option>
                    <option value="อาหารอีสาน">อาหารอีสาน</option>
                    <option value="อาหารทะเล">อาหารทะเล</option>
                    <option value="อาหารญี่ปุ่น">อาหารญี่ปุ่น</option>
                    <option value="อาหาตะวันตก">อาหารตะวันตก</option>
                    <option value="อาหารปักษ์ใต้">อาหารปักษ์ใต้</option>
                    <option value="อาหารจีน">อาหารจีน</option>
                    <option value="ของหวาน">ของหวาน</option>
                    <option value="อาหารริมทาง">อาหารริมทาง</option>
                    <option value="เครื่องดื่ม">เครื่องดื่ม</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>
            <button id="apply-filter">ค้นหา</button>
        </div>

        <div id="restaurant-list" class="restaurant-list">
            <p class="no-results">กำลังโหลดข้อมูลร้านอาหาร...</p>
        </div>
    </div>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/restaurants_list.js?v=<?php echo time(); ?>"></script>
</body>
</html>