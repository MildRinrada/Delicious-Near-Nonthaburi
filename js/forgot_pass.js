function goBack() {
    window.history.back();
}

function showError(inputElement, errorElement, message) {
    inputElement.classList.add('is-invalid');
    errorElement.textContent = message;
    errorElement.classList.add('show-error');
}

function hideError(inputElement, errorElement) {
    inputElement.classList.remove('is-invalid');
    errorElement.textContent = '';
    errorElement.classList.remove('show-error');
}

document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordForm = document.querySelector('.forgot-password-form');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    
    // ซ่อนข้อความ error เมื่อผู้ใช้เริ่มพิมพ์
    emailInput.addEventListener('input', function() {
        hideError(emailInput, emailError);
    });

    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

            let isValid = true;
            const email = emailInput.value.trim();

            // --- Client-side validation ---
            if (email === '') {
                showError(emailInput, emailError, 'กรุณากรอกอีเมล');
                isValid = false;
            } else if (!/^\S+@\S+\.\S+$/.test(email)) { // ตรวจสอบรูปแบบอีเมล
                showError(emailInput, emailError, 'รูปแบบอีเมลไม่ถูกต้อง');
                isValid = false;
            } else {
                hideError(emailInput, emailError);
            }

            if (isValid) {
                const formData = new FormData();
                formData.append('email', email);

                console.log("ส่งข้อมูลขอรีเซ็ตไปยัง forgot_pass_process.php", email);

                fetch('forgot_pass_process.php', { // ส่งไปยัง forgot_pass_process.php
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Non-JSON response from forgot_pass_process.php:', text);
                            throw new Error('Server did not return valid JSON. Check PHP errors in forgot_pass_process.php.');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message); 
                        window.location.href = 'message_sent.php';
                    } else {
                        console.error("Forgot password failed with message:", data.message);
                        showError(emailInput, emailError, data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during forgot password fetch:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                });
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const targetEmail = urlParams.get('email_target');

    // ฟังก์ชัน goBack()
    if (typeof goBack === 'undefined') { 
        window.goBack = function() {
            history.back(); 
        };
    }

    if (status === 'success_sent' && targetEmail) {       
        const userName = "ชื่อผู้ใช้";
        const resetLink = "http://YOUR_DOMAIN_NAME/verify_reset_token.php?token=...";
        const templateId = 'template_n7q2j4e';

        sendEmailViaEmailJS(decodeURIComponent(targetEmail), 'reset_password', {
            userName: userName,
            resetLink: resetLink
        })
        .then(() => {
            alert(`เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปยัง ${decodeURIComponent(targetEmail)} แล้ว กรุณาตรวจสอบอีเมลของคุณ`);
        })
        .catch(emailError => {
            alert("ไม่สามารถส่งอีเมลรีเซ็ตได้ กรุณาลองใหม่ (Error: " + emailError.message + ")");
            console.error('EmailJS send failed (on redirect success):', emailError);
        });

    } else if (status === 'email_not_found') {
        emailError.textContent = "ไม่พบอีเมลนี้ในระบบ กรุณาตรวจสอบอีกครั้ง";
        emailError.classList.add('show-error');
        emailInput.classList.add('is-invalid');
    } else if (status === 'db_error') {
        alert("มีข้อผิดพลาดในการสร้างลิงก์รีเซ็ต กรุณาลองใหม่ภายหลัง");
    }
});