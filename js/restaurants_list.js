document.addEventListener('DOMContentLoaded', function() {
    // --- ตัวแปรหลัก ---
    let allRestaurants = []; // เก็บข้อมูลร้านทั้งหมด
    let currentSortOrder = 'default'; // การเรียงลำดับปัจจุบัน

    const restaurantListDiv = document.getElementById('restaurant-list');
    const restaurantNameSearchInput = document.getElementById('restaurant-name-search');
    const districtDropdownBtn = document.getElementById('district-dropdown-btn');
    const districtDropdownContent = document.getElementById('district-filter-checkboxes');
    const foodTypeDropdownBtn = document.getElementById('food-type-dropdown-btn');
    const foodTypeDropdownContent = document.getElementById('food-type-filter-checkboxes');
    const sortDropdownBtn = document.getElementById('sort-dropdown-btn');
    const sortOptionsDiv = document.getElementById('sort-options');

    // ซิงค์ checkbox กับ URL ตอนโหลดหน้า ---
    applyFiltersFromURL();

    // โหลดข้อมูลร้านเมื่อหน้าโหลดและเวลาฟิลเตอร์เปลี่ยน ---
    fetchAndLoadAllRestaurants();

    // ฟังก์ชันดึงข้อมูลร้านจาก API ---
    async function fetchAndLoadAllRestaurants() {
        restaurantListDiv.innerHTML = '<p class="no-results">กำลังโหลดข้อมูลร้านอาหาร...</p>';
        try {
            const params = new URLSearchParams();

            // district[]
            const selectedDistricts = getSelectedCheckboxValues('district-filter-checkboxes');
            selectedDistricts.forEach(district => {
                if (district) params.append('district[]', district);
            });

            // food_type[] (ชื่อประเภทอาหาร)
            const selectedFoodTypes = getSelectedCheckboxValues('food-type-filter-checkboxes');
            selectedFoodTypes.forEach(foodType => {
                if (foodType) params.append('food_type[]', foodType);
            });

            // ชื่อร้าน
            if (restaurantNameSearchInput.value.trim() !== '') {
                params.append('restaurant_name', restaurantNameSearchInput.value.trim());
            }

            // sort
            if (currentSortOrder !== 'default') {
                params.append('sort', currentSortOrder);
            }

            const response = await fetch('../pages/get_restaurants.php?' + params.toString());

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const data = await response.json();
            if (data.success) {
                allRestaurants = data.restaurants;
                displayRestaurants(allRestaurants);
            } else {
                restaurantListDiv.innerHTML = `<p class="no-results error-message">${data.error || 'เกิดข้อผิดพลาด'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching restaurants:', error);
            restaurantListDiv.innerHTML = '<p class="no-results error-message">ไม่สามารถโหลดข้อมูลร้านอาหารได้ โปรดลองอีกครั้ง</p>';
        }
    }


    // ฟังก์ชันแสดงรายการร้าน
    function displayRestaurants(restaurants) {
    if (restaurants.length === 0) {
        restaurantListDiv.innerHTML = '<p class="no-results">ไม่พบร้านอาหารที่ตรงกับเงื่อนไข</p>';
        return;
    }

    let html = '';
    restaurants.forEach(r => {
        const name = htmlspecialchars(r.restaurant_name || 'ไม่ระบุชื่อร้าน');
        const district = htmlspecialchars(r.district || 'ไม่ระบุ');
        const rating = parseFloat(r.rating_avg || 0);
        const reviewCount = r.rating_count || 0;
        const rawStatus = (r.status || '').toLowerCase().trim();
        const restaurant_id = htmlspecialchars(r.restaurant_id?.toString() || '');

        // กำหนดคลาส status ตามค่าของสถานะร้าน
        let statusClass = 'unknown';
        if (/เปิด|open/.test(rawStatus)) {
            statusClass = 'open';
        } else if (/ปิด|closed/.test(rawStatus)) {
            statusClass = 'closed';
        } else {
            statusClass = 'unknown';
        }
        // แสดงสถานะต้นฉบับ (แต่อย่าลืม sanitize)
        const status = htmlspecialchars(r.status || 'ไม่ระบุสถานะ');

        const images = r.images && r.images.length > 0 ? r.images : ['static/images/default-restaurant.jpg'];
        const imageUrl = htmlspecialchars(images[0]);

        // สร้างดาว rating รองรับเลขทศนิยม (เต็มดาว + ครึ่งดาว)
        let starsHtml = '';
        const fullStars = Math.floor(rating);
        const halfStar = rating - fullStars >= 0.5;
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                starsHtml += '<span class="star-filled">&#9733;</span>';
            } else if (i === fullStars + 1 && halfStar) {
                starsHtml += '<span class="star-half">&#9733;</span>';
            } else {
                starsHtml += '<span class="star-empty">&#9733;</span>';
            }
        }

        // แยกประเภทอาหาร
        let foodTypes = [];
        if (r.food_type_name) {
            if (Array.isArray(r.food_type_name)) {
                foodTypes = r.food_type_name;
            } else if (typeof r.food_type_name === 'string') {
                foodTypes = r.food_type_name.split(',').map(s => s.trim());
            }
        }
        const foodTypesHtml = foodTypes.length
            ? foodTypes.map(ft => `<span class="food-type-tag" data-food-type="${htmlspecialchars(ft)}">${htmlspecialchars(ft)}</span>`).join(' ')
            : '<span class="food-type-tag">ไม่ระบุ</span>';

        html += `
            <a href="../pages/restaurant_detail.php?id=${restaurant_id}" class="restaurant-card-link">
                <div class="restaurant-card">
                    <img src="${imageUrl}" alt="${name}" class="restaurant-image" />
                    <div class="restaurant-card-content">
                        <h3>${name}</h3>
                        <p>อำเภอ: ${district}</p>
                        <div class="rating-display">${starsHtml} (${rating.toFixed(1)} / 5) (${reviewCount} รีวิว)</div>
                        <div>ประเภทอาหาร: ${foodTypesHtml}</div>
                        <div class="status ${statusClass}">${status}</div>
                    </div>
                </div>
            </a>
        `;
    });

    restaurantListDiv.innerHTML = html;

    // เพิ่ม event listener สำหรับคลิกที่ประเภทอาหาร
    restaurantListDiv.removeEventListener('click', handleRestaurantListClick);
    restaurantListDiv.addEventListener('click', handleRestaurantListClick);
}


    // ฟังก์ชัน escape HTML
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[m]);
    }

    // ฟังก์ชันดึงค่าที่ถูกเลือกใน checkbox
    function getSelectedCheckboxValues(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return [];
        const checkedBoxes = container.querySelectorAll('input[type="checkbox"]:checked');
        return Array.from(checkedBoxes).map(cb => cb.value);
    }

    // ฟังก์ชันจัดการคลิกที่ food type tag ในรายการร้าน 
    function handleRestaurantListClick(event) {
        const target = event.target;
        if (target.classList.contains('food-type-tag') && target.dataset.foodType) {
            event.preventDefault();
            event.stopPropagation();

            // ล้าง checkbox food type ทั้งหมด
            foodTypeDropdownContent.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

            // เลือก checkbox ที่ตรงกับ food type ที่คลิก
            const val = target.dataset.foodType;
            const cb = foodTypeDropdownContent.querySelector(`input[type="checkbox"][value="${val}"]`);
            if (cb) cb.checked = true;

            // ปิด dropdown
            foodTypeDropdownContent.classList.remove('show');
            if (foodTypeDropdownBtn) {
                foodTypeDropdownBtn.classList.remove('active');
                const arrow = foodTypeDropdownBtn.querySelector('.arrow');
                if (arrow) arrow.classList.remove('rotate');
            }

            // โหลดข้อมูลใหม่
            fetchAndLoadAllRestaurants();
        }
    }

    // โหลดข้อมูลใหม่เมื่อพิมพ์ค้นหาชื่อร้าน
    if (restaurantNameSearchInput) {
        restaurantNameSearchInput.addEventListener('input', () => {
            fetchAndLoadAllRestaurants();
        });
    }

    // โหลดข้อมูลใหม่เมื่อ checkbox district หรือ food type เปลี่ยน
    if (districtDropdownContent) {
        districtDropdownContent.addEventListener('change', fetchAndLoadAllRestaurants);
    }
    if (foodTypeDropdownContent) {
        foodTypeDropdownContent.addEventListener('change', fetchAndLoadAllRestaurants);
    }

    // ปุ่ม dropdown toggle (district, food type, sort)
    document.querySelectorAll('.dropbtn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation();
            const dropdownContent = this.nextElementSibling;
            const arrow = this.querySelector('.arrow');

            // ปิด dropdown อื่น ๆ
            document.querySelectorAll('.dropdown-content.show').forEach(openDropdown => {
                if (openDropdown !== dropdownContent) {
                    openDropdown.classList.remove('show');
                    const otherBtn = openDropdown.previousElementSibling;
                    if (otherBtn) {
                        otherBtn.classList.remove('active');
                        const otherArrow = otherBtn.querySelector('.arrow');
                        if (otherArrow) otherArrow.classList.remove('rotate');
                    }
                }
            });

            // toggle dropdown นี้
            dropdownContent.classList.toggle('show');
            this.classList.toggle('active');
            if (arrow) arrow.classList.toggle('rotate');
        });
    });

    // คลิกรอบนอก ปิด dropdown
    window.addEventListener('click', () => {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const btn = dropdown.querySelector('.dropbtn');
            const content = dropdown.querySelector('.dropdown-content');
            const arrow = btn ? btn.querySelector('.arrow') : null;
            if (content && content.classList.contains('show')) {
                content.classList.remove('show');
                if (btn) btn.classList.remove('active');
                if (arrow) arrow.classList.remove('rotate');
            }
        });
    });

    // เลือกการเรียงลำดับ
    if (sortOptionsDiv) {
        sortOptionsDiv.addEventListener('click', function(event) {
            event.preventDefault();
            const target = event.target;
            if (target.tagName === 'A' && target.dataset.sort) {
                sortOptionsDiv.querySelectorAll('.sort-option').forEach(option => option.classList.remove('active'));
                target.classList.add('active');

                currentSortOrder = target.dataset.sort || 'default';
                fetchAndLoadAllRestaurants();

                // อัปเดตข้อความปุ่มเรียงลำดับ
                if (sortDropdownBtn) {
                    sortDropdownBtn.innerHTML = `${target.textContent} <span class="arrow">▼</span>`;
                    sortDropdownBtn.classList.remove('active');
                    const arrow = sortDropdownBtn.querySelector('.arrow');
                    if (arrow) arrow.classList.remove('rotate');
                }
                sortOptionsDiv.classList.remove('show');
            }
        });
    }

    // ฟังก์ชันซิงค์ checkbox กับ URL parameter
    function applyFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);

        // สำหรับ food_type (array)
        let foodTypes = params.getAll('food_type');

        // ซิงค์ checkbox food type
        foodTypeDropdownContent.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = foodTypes.includes(cb.value);
        });

        // district (array)
        const districts = params.getAll('district');
        districtDropdownContent.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = districts.includes(cb.value);
        });

        // search name
        if (params.has('restaurant_name') && restaurantNameSearchInput) {
            restaurantNameSearchInput.value = params.get('restaurant_name');
        }
    }
});
