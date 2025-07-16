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
    const loginForm = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMeCheckbox = document.getElementById('rememberMe'); 
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    
    emailInput.addEventListener('input', function() {
        hideError(emailInput, emailError); 
    });
    passwordInput.addEventListener('input', function() {
        hideError(passwordInput, passwordError);
    });

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();

            let isValid = true;
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            const rememberMe = rememberMeCheckbox ? rememberMeCheckbox.checked : false; 

            if (email === '') {
                showError(emailInput, emailError, 'กรุณากรอกอีเมล'); 
                isValid = false;
            } else if (!/^\S+@\S+\.\S+$/.test(email)) { 
                showError(emailInput, emailError, 'รูปแบบอีเมลไม่ถูกต้อง'); 
                isValid = false;
            } else {
                hideError(emailInput, emailError); 
            }

            if (password === '') {
                showError(passwordInput, passwordError, 'กรุณากรอกรหัสผ่าน'); 
            } else {
                hideError(passwordInput, passwordError); 
            }

            if (isValid) {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                formData.append('rememberMe', rememberMe ? 'true' : 'false'); 

                console.log("ส่งข้อมูล login ไปที่ login_process.php", email, password, rememberMe);

                fetch('login_process.php', { 
                    method: 'POST',
                    body: formData,
                    credentials: 'include' 
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
                            showError(passwordInput, passwordError, data.message); // <--- แก้ไขตรงนี้
                        } else if (data.message.includes('ไม่พบอีเมลนี้ในระบบ')) {
                            showError(emailInput, emailError, data.message); // <--- แก้ไขตรงนี้
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