// สไลเดอร์สำหรับ Ads (แยกจาก Promotion)
document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.ads-slider-container .ads1');
    const totalSlides = slides.length;

    if (totalSlides === 0) {
        console.log("No slides found for Ads Slider. Check your HTML class names.");
        return; // ถ้าไม่มีสไลด์หยุดทำงาน
    }

    // แสดงสไลด์ที่ index ที่กำหนด
    function showSlide(index) {
        slides.forEach(slide => {
            slide.classList.remove('active-slide'); // ซ่อนสไลด์ทุกตัว
        });
        slides[index].classList.add('active-slide'); // แสดงสไลด์ปัจจุบัน
    }

    // เลื่อนไปสไลด์ถัดไป (วนกลับไปที่แรกเมื่อสุด)
    function nextSlide() {
        slideIndex++;
        if (slideIndex >= totalSlides) {
            slideIndex = 0;
        }
        showSlide(slideIndex);
    }

    showSlide(slideIndex); // เริ่มต้นแสดงสไลด์แรก
    setInterval(nextSlide, 3000); // เปลี่ยนสไลด์ทุก 3 วินาที
});

// สไลเดอร์สำหรับ Promotion (แยกจาก Ads)
document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.promotion-slider-container .ads1');
    const totalSlides = slides.length;

    if (totalSlides === 0) {
        console.log("No slides found for Promotion Slider. Check your HTML class names.");
        return; // ถ้าไม่มีสไลด์หยุดทำงาน
    }

    // แสดงสไลด์ที่ index ที่กำหนด
    function showSlide(index) {
        slides.forEach(slide => {
            slide.classList.remove('active-slide');
        });
        slides[index].classList.add('active-slide');
    }

    // เลื่อนไปสไลด์ถัดไป (วนกลับไปที่แรกเมื่อสุด)
    function nextSlide() {
        slideIndex++;
        if (slideIndex >= totalSlides) {
            slideIndex = 0;
        }
        showSlide(slideIndex);
    }

    showSlide(slideIndex);
    setInterval(nextSlide, 3000);
});

// ฟังก์ชันส่งอีเมลผ่าน EmailJS
function sendMail(event) {
    if (event) {
        event.preventDefault(); // ป้องกันการรีเฟรชหน้าเมื่อ submit ฟอร์ม
    }

    // รับค่าจาก input ฟอร์ม
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;

    // ตรวจสอบว่ากรอกครบทุกช่องหรือไม่
    if (!name || !email || !subject || !message) {
        alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
        return;
    }

    // เตรียมข้อมูลสำหรับส่ง
    const formData = {
        from_name: name,
        from_email: email,
        subject: subject, 
        message: message,
    };

    console.log('ข้อมูลฟอร์มที่ได้:', formData);

    // ส่งอีเมลผ่าน emailjs
    emailjs.send('service_i26lbtb', 'template_o88y5un', formData)
        .then(function(response) {
            console.log('SUCCESS!', response.status, response.text);
            alert('ส่งข้อความเรียบร้อยแล้ว! ขอบคุณค่า');
            document.getElementById('myForm').reset(); // ล้างฟอร์มหลังส่งสำเร็จ
        }, function(error) {
            console.error('FAILED', error);
            alert('เกิดข้อผิดพลาดในการส่งข้อความ กรุณาลองใหม่อีกครั้ง');
        });
}
