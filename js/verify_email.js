// ../js/verify_email.js

document.addEventListener('DOMContentLoaded', function() {
    const verificationCodeInput = document.getElementById('verification-code');
    const codeErrorMessage = document.getElementById('code-error');
    const verifyEmailForm = document.querySelector('.verify-email-form');
    const resendCodeLink = document.getElementById('resend-code-link');
    const userEmailDisplay = document.getElementById('user-email-display'); 
    const emailToVerifyInput = document.getElementById('emailToVerify'); // อ้างอิง hidden input

    // ฟังก์ชันสำหรับแสดง/ซ่อนข้อความ Error
    function showCodeError(message) {
        codeErrorMessage.textContent = message;
        codeErrorMessage.classList.add('show-error');
        verificationCodeInput.classList.add('is-invalid'); // เพิ่ม class ให้ input เป็นสีแดง
    }

    function hideCodeError() {
        codeErrorMessage.textContent = '';
        codeErrorMessage.classList.remove('show-error');
        verificationCodeInput.classList.remove('is-invalid');
    }

    // ตรวจสอบรหัสยืนยันเมื่อผู้ใช้เริ่มพิมพ์
    verificationCodeInput.addEventListener('input', function() {
        const code = verificationCodeInput.value.trim();
        if (code === '' || (code.length === 6 && /^\d{6}$/.test(code))) {
            hideCodeError();
        }
    });

    // ตรวจสอบฟอร์มเมื่อกด Submit
    if (verifyEmailForm) {
        verifyEmailForm.addEventListener('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มทันที

            let isValid = true;
            const code = verificationCodeInput.value.trim();
            const email = emailToVerifyInput.value.trim(); // ดึงอีเมลจาก hidden input

            // ตรวจสอบว่ารหัสยืนยันไม่ว่างเปล่าและเป็นตัวเลข 6 หลัก
            if (code === '') {
                showCodeError('กรุณากรอกรหัสยืนยัน');
                isValid = false;
            } else if (!/^\d{6}$/.test(code)) {
                showCodeError('รหัสยืนยันต้องเป็นตัวเลข 6 หลัก');
                isValid = false;
            } else {
                hideCodeError();
            }

            if (isValid) {
                // ถ้าข้อมูลถูกต้อง ให้ส่งข้อมูลไปยัง Back-end ด้วย fetch API
                const formData = new FormData();
                formData.append('email', email);
                formData.append('verification_code', code);

                fetch('verify_email_process.php', { // ส่งไปยัง verify_email_process.php
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Non-JSON response from verify_email_process.php:', text);
                            throw new Error('Server did not return valid JSON. Check PHP errors.');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        // *** จุดที่แก้ไข: แสดง alert ก่อน แล้วค่อย Redirect ***
                        alert(data.message); // แสดงป๊อปอัพด้วยข้อความจาก PHP
                        window.location.href = 'login.php'; // วาร์ปไปหน้า login.php
                    } else {
                        // รหัสผิด: แสดงข้อความ Error สีแดง
                        showCodeError(data.message); // แสดงข้อความ Error จาก PHP
                    }
                })
                .catch(error => {
                    console.error('Error during verification fetch:', error);
                    showCodeError('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                });
            }
        });
    }

    
    if (resendCodeLink) {
        resendCodeLink.addEventListener('click', function(event) {
            event.preventDefault();

            const emailToResend = emailToVerifyInput.value.trim(); // ดึงอีเมลจาก hidden input

            if (emailToResend) {
                // ส่งอีเมลไปยัง resend_code_process.php
                fetch('resend_code_process.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(emailToResend)
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Non-JSON response from resend_code_process.php:', text);
                            throw new Error('Server did not return valid JSON. Check PHP errors.');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        sendVerificationEmail(data.email, data.verification_code, 'template_ejr3erd', data.firstname) 
                            .then(() => {
                                alert('ส่งรหัสยืนยันใหม่ไปยังอีเมลของคุณแล้ว');
                                // ปิดการใช้งานลิงก์ชั่วคราว
                                resendCodeLink.style.pointerEvents = 'none';
                                resendCodeLink.style.opacity = '0.5';
                                setTimeout(() => {
                                    resendCodeLink.style.pointerEvents = 'auto';
                                    resendCodeLink.style.opacity = '1';
                                }, 60000); // 1 นาที
                            })
                            .catch(emailError => {
                                console.error('Error sending email via EmailJS (resend):', emailError);
                                alert('ส่งรหัสใหม่สำเร็จ แต่ไม่สามารถส่งอีเมลได้: ' + (emailError.message || 'Unknown error') + '\nกรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ');
                            });
                    } else {
                        alert('เกิดข้อผิดพลาดในการส่งรหัสใหม่: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                });
            } else {
                alert('ไม่พบอีเมลผู้ใช้ กรุณากลับไปหน้าลงทะเบียนใหม่');
            }
        });
    }
});