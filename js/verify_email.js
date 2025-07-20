document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("QOtzQR-DwANTtEMz9");

    const verificationCodeInput = document.getElementById('verification-code');
    const codeErrorMessage = document.getElementById('code-error');
    const verifyEmailForm = document.querySelector('.verify-email-form');
    const resendCodeLink = document.getElementById('resend-code-link');
    const userEmailDisplay = document.getElementById('user-email-display'); 
    const emailToVerifyInput = document.getElementById('emailToVerify'); // อ้างอิง hidden input

    function showCodeError(message) {
        codeErrorMessage.textContent = message;
        codeErrorMessage.classList.add('show-error');
        verificationCodeInput.classList.add('is-invalid'); 
    }

    function hideCodeError() {
        codeErrorMessage.textContent = '';
        codeErrorMessage.classList.remove('show-error');
        verificationCodeInput.classList.remove('is-invalid');
    }

    verificationCodeInput.addEventListener('input', function() {
        const code = verificationCodeInput.value.trim();
        if (code === '' || (code.length === 6 && /^\d{6}$/.test(code))) {
            hideCodeError();
        }
    });

    if (verifyEmailForm) {
        verifyEmailForm.addEventListener('submit', function(event) {
            event.preventDefault();

            let isValid = true;
            const code = verificationCodeInput.value.trim();
            const email = emailToVerifyInput.value.trim();

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
                const formData = new FormData();
                formData.append('email', email);
                formData.append('verification_code', code);

                fetch('verify_email_process.php', {
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
                        alert(data.message);
                        window.location.href = 'login.php';
                    } else {
                        showCodeError(data.message);
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

            const emailToResend = emailToVerifyInput.value.trim();

            if (emailToResend) {
                fetch('resend_code_process.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
                                resendCodeLink.style.pointerEvents = 'none';
                                resendCodeLink.style.opacity = '0.5';
                                setTimeout(() => {
                                    resendCodeLink.style.pointerEvents = 'auto';
                                    resendCodeLink.style.opacity = '1';
                                }, 60000);
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

    function sendVerificationEmail(email, code, templateId, firstname) {
        return emailjs.send("service_10mwfcr", templateId, {
            to_email: email,
            to_name: firstname,
            verification_code: code
        });
    }
});
