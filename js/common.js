// โหลด HTML component ภายนอกเข้า element ที่กำหนด
function loadComponent(elementId, componentPath) {
    fetch(componentPath)
        .then(response => response.text())
        .then(html => {
            document.getElementById(elementId).innerHTML = html;
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาดในการโหลด Component:', error);
        });
}

// เมื่อหน้าเว็บโหลดเสร็จ ให้ตั้งค่า smooth scroll สำหรับลิงก์ที่มีคลาส .smooth-scroll
document.addEventListener('DOMContentLoaded', function() {
    const smoothScrollLinks = document.querySelectorAll('a.smooth-scroll');

    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // ยกเลิกพฤติกรรมเปลี่ยนหน้าปกติ

            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            // เลื่อนหน้าไปยัง element เป้าหมายแบบนุ่มนวล
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start' 
                });
            }
        });
    });
});

// ส่งอีเมลผ่าน EmailJS โดยเลือกรูปแบบตาม templateType (เช่น ยืนยันตัวตน หรือรีเซ็ตรหัสผ่าน)
function sendEmailViaEmailJS(recipientEmail, templateType, data) {
    let templateId;
    let serviceId;
    let templateParams = {
        to_email: recipientEmail,
        reply_to_email: recipientEmail
    };

    // ตั้งค่า template และข้อมูลตามประเภทอีเมล
    if (templateType === 'verification') {
        templateId = 'template_ejr3erd';
        serviceId = 'service_10mwfcr';
        templateParams.verification_code = data.verificationCode;
        templateParams.Name = data.userName || 'ผู้ใช้งาน';
    } else if (templateType === 'reset_password') {
        templateId = 'template_n7q2j4e';
        serviceId = 'service_gszdfxo';
        templateParams.Name = data.userName || 'ผู้ใช้งาน';
        templateParams.reset_link = data.resetLink;
    } else {
        console.error("Unknown template type:", templateType);
        return Promise.reject("Unknown template type");
    }

    // ตรวจสอบว่า serviceId มีค่าหรือไม่
    if (!serviceId) {
        console.error("Service ID not defined for template type:", templateType);
        return Promise.reject("Service ID not defined");
    }

    // เรียกใช้ emailjs เพื่อส่งอีเมล
    return emailjs.send(serviceId, templateId, templateParams)
        .then(function(response) {
            console.log('EmailJS SUCCESS!', response.status, response.text);
            return true;
        }, function(error) {
            console.error('EmailJS FAILED...', error);
            throw error;
        });
}
