// ไฟล์: index.jss
document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.ads-slider-container .ads1');
    const totalSlides = slides.length;

    if (totalSlides === 0) {
        console.log("No slides found for Ads Slider. Check your HTML class names.");
        return;
    }

    function showSlide(index) {
        slides.forEach(slide => {
            slide.classList.remove('active-slide');
        });
        slides[index].classList.add('active-slide');
    }

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

// --- โค้ดสำหรับ Promotion Slider (อยู่ภายใน DOMContentLoaded ของตัวเอง) ---
document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.promotion-slider-container .ads1');
    const totalSlides = slides.length;

    if (totalSlides === 0) {
        console.log("No slides found for Promotion Slider. Check your HTML class names.");
        return;
    }

    function showSlide(index) {
        slides.forEach(slide => {
            slide.classList.remove('active-slide');
        });
        slides[index].classList.add('active-slide');
    }

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

// --- ฟังก์ชัน sendMail ควรอยู่ตรงนี้ (Global Scope) ---
function sendMail(event) {
    if (event) {
        event.preventDefault();
    }

    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;

    if (!name || !email || !subject || !message) {
        alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
        return;
    }

    const formData = {
        from_name: name,
        from_email: email,
        subject: subject, 
        message: message,
    };

    console.log('ข้อมูลฟอร์มที่ได้:', formData);

    emailjs.send('service_i26lbtb', 'template_o88y5un', formData)
        .then(function(response) {
            console.log('SUCCESS!', response.status, response.text);
            alert('ส่งข้อความเรียบร้อยแล้ว! ขอบคุณค่า');
            document.getElementById('myForm').reset();
        }, function(error) {
            console.error('FAILED', error);
            alert('เกิดข้อผิดพลาดในการส่งข้อความ กรุณาลองใหม่อีกครั้ง');
        });
}