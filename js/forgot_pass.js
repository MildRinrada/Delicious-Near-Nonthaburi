// ฟังก์ชันย้อนกลับไปหน้าก่อนหน้า
function goBack() {
    window.history.back();
}

// แสดงข้อความ error สำหรับ input และแสดง style
function showError(inputElement, errorElement, message) {
    inputElement.classList.add('is-invalid');
    errorElement.textContent = message;
    errorElement.classList.add('show-error');
}

// ซ่อนข้อความ error และลบ style ที่ใช้แสดง error
function hideError(inputElement, errorElement) {
    inputElement.classList.remove('is-invalid');
    errorElement.textContent = '';
    errorElement.classList.remove('show-error');
}

// รอให้ DOM โหลดเสร็จแล้วค่อยทำงาน
document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordForm = document.querySelector('.forgot-password-form');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    
    // ซ่อน error ทันทีเมื่อผู้ใช้เริ่มพิมพ์
    emailInput.addEventListener('input', function() {
        hideError(emailInput, emailError);
    });

    if (forgotPasswordForm) {
        // เมื่อผู้ใช้ส่งฟอร์ม
        forgotPasswordForm.addEventListener('submit', function(event) {
            event.preventDefault(); // ป้องกัน reload หน้า

            let isValid = true;
            const email = emailInput.value.trim();

            // ตรวจสอบความถูกต้องของอีเมล
            if (email === '') {
                showError(emailInput, emailError, 'กรุณากรอกอีเมล');
                isValid = false;
            } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                showError(emailInput, emailError, 'รูปแบบอีเมลไม่ถูกต้อง');
                isValid = false;
            } else {
                hideError(emailInput, emailError);
            }

            if (isValid) {
                // สร้าง FormData เพื่อส่งไป PHP
                const formData = new FormData();
                formData.append('email', email);

                console.log("ส่งข้อมูลขอรีเซ็ตไปยัง forgot_pass_process.php", email);

                // ส่งคำขอไปยัง PHP ผ่าน fetch
                fetch('forgot_pass_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Non-JSON response:', text);
                            throw new Error('Server did not return JSON');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message); 
                        window.location.href = 'message_sent.php'; // ไปหน้าถัดไป
                    } else {
                        showError(emailInput, emailError, data.message); // แสดง error จาก server
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                });
            }
        });
    }
});

// เช็ค query parameter บน URL และทำตามเงื่อนไขที่เจอ
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const targetEmail = urlParams.get('email_target');

    // หากยังไม่มี goBack ให้กำหนดไว้
    if (typeof goBack === 'undefined') { 
        window.goBack = function() {
            history.back(); 
        };
    }

    // หากมีสถานะ success_sent และมี email ส่งเมลผ่าน emailJS
    if (status === 'success_sent' && targetEmail) {       
        const userName = "ชื่อผู้ใช้"; // กำหนดชื่อผู้ใช้ (กรณีไม่มีข้อมูลจริง)
        const resetLink = "http://YOUR_DOMAIN_NAME/verify_reset_token.php?token=...";
        const templateId = 'template_n7q2j4e';

        sendEmailViaEmailJS(decodeURIComponent(targetEmail), 'reset_password', {
            userName: userName,
            resetLink: resetLink
        })
        .then(() => {
            alert(`เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปยัง ${decodeURIComponent(targetEmail)} แล้ว`);
        })
        .catch(emailError => {
            alert("ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่ (" + emailError.message + ")");
        });

    } else if (status === 'email_not_found') {
        // แสดง error หากอีเมลไม่พบในระบบ
        emailError.textContent = "ไม่พบอีเมลนี้ในระบบ กรุณาตรวจสอบอีกครั้ง";
        emailError.classList.add('show-error');
        emailInput.classList.add('is-invalid');
    } else if (status === 'db_error') {
        // แจ้ง error กรณีเซิร์ฟเวอร์มีปัญหา
        alert("มีข้อผิดพลาดในการสร้างลิงก์รีเซ็ต กรุณาลองใหม่ภายหลัง");
    }
});
