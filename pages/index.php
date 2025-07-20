<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/index.css?v=<?php echo time(); ?>">
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


    <section>
        <div class="Section1">
            <div class="left-content">
                <p class="searchSection1">หาของกินใกล้ๆ<br>ในนนทบุรี</p>
                <div class="search-container"> 
                    <input type="text" id="search-input" placeholder="คลิกเพื่อค้นหาร้านอาหารหรือเมนู...">
                    <span class="search-icon">🔍</span>
                </div>
                <div class="button-group"> 
                    <button class="button1" style="margin-left: 4em;">ร้านแนะนำ</button>
                    <button class="button1" style="margin-left: 4em;">ร้านอาหารยอดนิยม</button>
                    <a href="restaurants_list.php"><button class="button1" style="margin-left: 4em;">ดูร้านอาหารทั้งหมด</button></a>
                </div>
            </div>
            <div class="right-image"> 
                <img src="../static/images/fork and spoon.png" alt="โลโก้ อร่อยใกล้นนท์" id="main-logo-section">
            </div>
        </div>
    </section>

    <section class="Section2">
        <div class="slider-section-wrapper"> 
            <div class="promotion-section"> 
                <p class="PromotionTitle">โปรโมชั่นสุดพิเศษ</p>
                <div class="promotion-slider-container">
                    <a href="www.google.com" class="ads1 active-slide">
                        <img src="../static/images/promotion/promotion1.png" class="promotion1">
                    </a>
                    <a href="www.google.com" class="ads1"> 
                        <img src="../static/images/promotion/promotion1.png" class="promotion1"> 
                    </a>
                </div>
            </div>

            <div class="ads-section">
                <div class="ads-slider-container">
                    <a href="https://marketeeronline.co/archives/168305" class="ads1 active-slide">
                        <img src="../static/images/ads/ads_1.png" class="promotion1" id="promo-slide-1">
                    </a>
                    <a href="https://pin.it/6C8ESR7UA" class="ads1">
                        <img src="../static/images/ads/ads_2.png" class="promotion1" id="promo-slide-2">
                    </a>
                    <a href="https://brandinside.asia/oishi-green-tea-ads-6-seconds/" class="ads1">
                        <img src="../static/images/ads/ads_3.png" class="promotion1" id="promo-slide-3">
                    </a>
                </div>
            </div>
        </div>
        <br>
    <section>

    <?php
        $food_type_id = $_GET['food_type_id'] ?? null;
    ?>
    <section class="Section3">
        <p class="CategoryTitle">หาของกินแบบไหนเอ่ย?</p>
        <div class="category-grid">
            <div class="col-1-4" >
                <a href="restaurants_list.php?food_type=อาหารจานเดียว">
                    <img src="../static/images/category/อาหารจานเดียว.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารจานเดียว">
                    <button class="button1">อาหารจานเดียว</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารตามสั่ง">
                    <img src="../static/images/category/อาหารตามสั่ง.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารตามสั่ง">
                    <button class="button1">อาหารตามสั่ง</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=ก๋วยเตี๋ยว">
                    <img src="../static/images/category/ก๋วยเตี๋ยว.png">
                </a>
                <a href="restaurants_list.php?food_type=ก๋วยเตี๋ยว">
                    <button class="button1">ก๋วยเตี๋ยว</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารอีสาน">
                    <img src="../static/images/category/อาหารอีสาน.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารอีสาน">
                    <button class="button1">อาหารอีสาน</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารทะเล">
                    <img src="../static/images/category/อาหารทะเล.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารทะเล">
                    <button class="button1">อาหารทะเล</button>
                </a>
            </div>            
        </div>
        <div class="category-grid">
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <img src="../static/images/category/อาหารญี่ปุ่น.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <button class="button1">อาหารญี่ปุ่น</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <img src="../static/images/category/อาหารตะวันตก.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <button class="button1">อาหารตะวันตก</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <img src="../static/images/category/อาหารปักษ์ใต้.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารญี่ปุ่น">
                    <button class="button1">อาหารปักษ์ใต้</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=อาหารจีน">
                    <img src="../static/images/category/อาหารจีน.png">
                </a>
                <a href="restaurants_list.php?food_type=อาหารจีน">
                    <button class="button1">อาหารจีน</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="restaurants_list.php?food_type=ของหวาน">
                    <img src="../static/images/category/ของหวาน.png">
                </a>                
                <a href="restaurants_list.php?food_type=ของหวาน">
                    <button class="button1">ของหวาน</button>
                </a>
            </div>
        </div>
        <div class="category-grid">
            <div class="col-1-4">
                <a href="">
                    <img src="../static/images/category/อาหารริมทาง.png">
                </a>                
                <a href="">
                    <button class="button1">อาหารริมทาง</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="">
                    <img src="../static/images/category/เครื่องดื่ม.png">
                </a>                
                <a href="">
                    <button class="button1">เครื่องดื่ม</button>
                </a>
            </div>
            <div class="col-1-4">
                <a href="">
                    <img src="../static/images/category/อื่นๆ.png">
                </a>                
                <a href="">
                    <button class="button1">อื่นๆ</button>
                </a>
            </div>
        </div>        
    </section>

    <section class="Section4">
    <!-- Contact Section -->
        <div id="contact">
            <div class="grid grid-pad">
                <p class="ContactTitle">ติดต่อเรา</p>
                <div class="col-1-2">
                    <div class="content address">
                        <address>
                            <div>
                                <div class="box-icon2">
                                    <img src="../static/images/icon/email.png" alt="ไอคอนอีเมล" width="25" height="auto">
                                </div>
                                <div class="text-content"> 
                                    <span class="email-label">อีเมล:</span>
                                    <p class="email-address">mildxxxx@gmail.com</p>
                                </div>
                            </div>
                        </address>
                    </div>
                </div>
                <div class="col-1-2 pleft-25" >
                    <div class="content contact-form">
                        <form class="form contact_form" id="myForm" onsubmit="sendMail(event)">
                            <input type="text" id="name" class="second-input" type="name" style="font-size: 14px; color: #4F2B14;" placeholder="ชื่อผู้ส่ง*" required>
                            <input type="text" id="email" class="second-input" type="email" style="font-size: 14px; color: #4F2B14;" placeholder="อีเมล*" required>
                            <input type="text" id="subject" class="second-input" type="title" style="font-size: 14px; color: #4F2B14;" placeholder="หัวเรื่อง*" required>
                            <textarea id="message" class="second-input" type="msg" style="font-size: 14px; color: #141719;" placeholder="ข้อความ..." required></textarea>                             
                            <input type="submit" value="ส่งข้อความ" class="button1"> 
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Contact Section -->

    <?php include('components/footer.html'); ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script type="text/javascript">
       (function() {
          emailjs.init('QOtzQR-DwANTtEMz9');
       })();
    </script>
    <script src="../js/index.js"></script>
    <script src="../js/common.js"></script>

     <script>
    const foodTypeId = <?php echo json_encode($food_type_id); ?>;

    async function fetchAndLoadRestaurants(foodTypeId) {
      const container = document.getElementById('restaurant-list');
      container.innerHTML = 'กำลังโหลดข้อมูล...';

      try {
        const response = await fetch(`../pages/get_restaurants.php?food_type_id=${foodTypeId}`);
        const data = await response.json();
        if (data.success) {
          if (data.restaurants.length === 0) {
            container.innerHTML = 'ไม่พบร้านอาหารประเภทนี้';
          } else {
            let html = '';
            data.restaurants.forEach(r => {
              html += `<div>${r.restaurant_name}</div>`;
            });
            container.innerHTML = html;
          }
        } else {
          container.innerHTML = 'เกิดข้อผิดพลาด: ' + data.error;
        }
      } catch (e) {
        container.innerHTML = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
      }
    }

    if (foodTypeId) {
      fetchAndLoadRestaurants(foodTypeId);
    } else {
      document.getElementById('restaurant-list').innerHTML = 'กรุณาเลือกประเภทอาหาร';
    }

    function loadRestaurants(foodTypeId) {
    fetch(`/Delicious Near Nonthaburi/pages/get_restaurants.php?food_type_id=${foodTypeId}`)
        .then(response => response.json())
        .then(data => {
        console.log(data);

        const listDiv = document.getElementById('restaurant-list');
        listDiv.innerHTML = '';

        if (!Array.isArray(data.restaurants) || data.restaurants.length === 0) {
            listDiv.innerHTML = '<p>ไม่พบร้านอาหาร</p>';
            return;
        }

        data.restaurants.forEach(restaurant => {
            const item = document.createElement('div');
            item.className = 'restaurant-card';
            item.innerHTML = `
                <h3>${restaurant.name}</h3>
                <p>${restaurant.description}</p>
                <p>ที่อยู่: ${restaurant.address}</p>
                <p>ประเภท: ${restaurant.food_types}</p>
            `;
            listDiv.appendChild(item);
        });
    })
    }

  </script>
</body>
</html>