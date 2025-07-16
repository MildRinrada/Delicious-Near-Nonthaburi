 document.addEventListener('DOMContentLoaded', function() {
            const restaurantListDiv = document.getElementById('restaurant-list');
            const districtFilter = document.getElementById('district-filter');
            const foodTypeFilter = document.getElementById('food-type-filter');
            const applyFilterButton = document.getElementById('apply-filter');

            // ฟังก์ชันสำหรับดึงและแสดงข้อมูลร้านอาหาร
            function fetchAndDisplayRestaurants() {
                const selectedDistrict = districtFilter.value;
                const selectedFoodType = foodTypeFilter.value;

                // สร้าง URL สำหรับเรียก API ของคุณ
                // *** สำคัญ: ตรวจสอบให้แน่ใจว่า 'get_restaurants.php' อยู่ใน Path ที่ถูกต้องเมื่อเทียบกับไฟล์ restaurants_list.php นี้ ***
                // ถ้า get_restaurants.php อยู่ในโฟลเดอร์เดียวกันกับ restaurants_list.php ก็ใช้แค่ 'get_restaurants.php'
                // ถ้า get_restaurants.php อยู่ในโฟลเดอร์ย่อย เช่น 'api/' คุณอาจต้องใช้ 'api/get_restaurants.php'
                let url = `get_restaurants.php?`; 

                if (selectedDistrict) {
                    url += `district=${encodeURIComponent(selectedDistrict)}&`;
                }
                if (selectedFoodType) {
                    url += `food_type=${encodeURIComponent(selectedFoodType)}&`;
                }
                // ลบ '&' สุดท้ายถ้ามี เพื่อไม่ให้ URL มี '&' เกิน
                url = url.endsWith('&') ? url.slice(0, -1) : url;

                // แสดงข้อความ "กำลังโหลด" ขณะรอข้อมูล
                restaurantListDiv.innerHTML = '<p class="no-results">กำลังโหลดข้อมูลร้านอาหาร...</p>';

                // ใช้ Fetch API ใน JavaScript เพื่อเรียกข้อมูลจาก PHP
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
    if (data.success && data.restaurants && data.restaurants.length > 0) {
        restaurantListDiv.innerHTML = '';
        data.restaurants.forEach(restaurant => {
            const link = document.createElement('a');
            link.href = `restaurant_detail.php?id=${restaurant.id}`;
            link.className = 'restaurant-card-link';

            const card = document.createElement('div');
            card.className = 'restaurant-card';

            // *** เพิ่มโค้ด 2 ส่วนนี้เข้ามาตรงนี้เลยครับ ***

            // 1. กำหนด URL รูปภาพ
            const imageUrl = restaurant.images && restaurant.images.length > 0 ? restaurant.images[0] : 'assets/placeholder.png'; // ใช้รูปแรกจาก array หรือรูป placeholder

            // 2. กำหนด Class สำหรับสถานะ
            let statusClass = 'unknown';
            if (restaurant.status.includes('เปิดอยู่')) {
                statusClass = 'open';
            } else if (restaurant.status.includes('ปิด')) {
                statusClass = 'closed';
            }

            card.innerHTML = `
                <img src="${imageUrl}" alt="${restaurant.name}">
                <div class="restaurant-card-content">
                    <h3>${restaurant.name}</h3>
                    <p><strong>ประเภท:</strong> ${restaurant.food_types || 'ไม่ระบุ'}</p>
                    <p class="rating"><strong>คะแนน:</strong> ${restaurant.rating_avg || 'N/A'} (${restaurant.rating_count || 0} รีวิว)</p>
                    <p><strong>อำเภอ:</strong> ${restaurant.district || 'ไม่ระบุ'}</p>
                    <p class="status ${statusClass}"><strong>สถานะ:</strong> ${restaurant.status}</p>
                </div>
            `;
            link.appendChild(card);
            restaurantListDiv.appendChild(link);
        });

    } else {
                            restaurantListDiv.innerHTML = '<p class="no-results">ไม่พบร้านอาหารที่ตรงตามเงื่อนไข หรือเกิดข้อผิดพลาด</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching or parsing restaurants data:', error);
                        restaurantListDiv.innerHTML = '<p class="no-results">เกิดข้อผิดพลาดในการดึงข้อมูลร้านอาหาร. โปรดลองอีกครั้งในภายหลัง</p>';
                    });
            }

            // เพิ่ม Event Listener ให้ปุ่ม "ค้นหา"
            applyFilterButton.addEventListener('click', fetchAndDisplayRestaurants);

            // โหลดร้านอาหารครั้งแรกเมื่อหน้าเว็บโหลดเสร็จ
            fetchAndDisplayRestaurants();
        });