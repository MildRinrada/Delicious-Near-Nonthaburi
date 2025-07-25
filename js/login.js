// ฟังก์ชันย้อนกลับไปหน้าก่อนหน้า
function goBack() {
    window.history.back();
}

// แสดงข้อความผิดพลาดและใส่คลาสแดงกับ input ที่ผิด
function showError(inputElement, errorElement, message) {
    inputElement.classList.add('is-invalid');
    errorElement.textContent = message;
    errorElement.classList.add('show-error');
}

// ซ่อนข้อความผิดพลาดและลบคลาสแดงออก
function hideError(inputElement, errorElement) {
    inputElement.classList.remove('is-invalid');
    errorElement.textContent = '';
    errorElement.classList.remove('show-error');
}

document.addEventListener('DOMContentLoaded', function() {
    // อ้างอิง element ที่เกี่ยวข้องในฟอร์ม login
    const loginForm = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMeCheckbox = document.getElementById('rememberMe'); 
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    
    // ซ่อน error เมื่อผู้ใช้เริ่มแก้ไข input
    emailInput.addEventListener('input', function() {
        hideError(emailInput, emailError); 
    });
    passwordInput.addEventListener('input', function() {
        hideError(passwordInput, passwordError);
    });

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มแบบเดิม

            let isValid = true;
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            const rememberMe = rememberMeCheckbox ? rememberMeCheckbox.checked : false; 

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

            // ตรวจสอบว่ารหัสผ่านไม่ว่าง
            if (password === '') {
                showError(passwordInput, passwordError, 'กรุณากรอกรหัสผ่าน'); 
                isValid = false;
            } else {
                hideError(passwordInput, passwordError); 
            }

            if (isValid) {
                // สร้าง FormData สำหรับส่งข้อมูล POST
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                formData.append('rememberMe', rememberMe ? 'true' : 'false'); 

                console.log("ส่งข้อมูล login ไปที่ login_process.php", email, password, rememberMe);

                fetch('login_process.php', { 
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // ส่ง cookie ด้วย
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Non-JSON response from login_process.php:', text);
                            throw new Error('Server did not return valid JSON. Check PHP errors in login_process.php.');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message); 
                        console.log("Login successful, redirecting to:", data.redirect);
                        window.location.href = data.redirect; 
                    } else {
                        console.log("Login failed with message:", data.message);
                        if (data.unverified) {
                            alert(data.message); 
                        } else if (data.message.includes('รหัสผ่านไม่ถูกต้อง')) {
                            showError(passwordInput, passwordError, data.message);
                        } else if (data.message.includes('ไม่พบอีเมลนี้ในระบบ')) {
                            showError(emailInput, emailError, data.message);
                        } else {
                            alert(data.message); 
                        }
                    }
                })
                .catch(error => {
                    console.error('Error during login fetch:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                });
            }
        });
    }
});
