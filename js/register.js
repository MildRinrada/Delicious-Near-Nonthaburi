
document.addEventListener('DOMContentLoaded', function() {
    // จัดการ Checkbox ประเภทอาหาร
    const foodTypeCheckboxesInitial = document.querySelectorAll('input[name="food_type[]"]'); 
    foodTypeCheckboxesInitial.forEach(checkbox => {
        if (checkbox.checked) {
            checkbox.closest('.custom-checkbox-large').classList.add('checked');
        }
        checkbox.addEventListener('change', function() {
            const parentLabel = this.closest('.custom-checkbox-large');
            if (this.checked) {
                parentLabel.classList.add('checked');
            } else {
                parentLabel.classList.remove('checked');
            }
        });
    });

    // อ้างอิง Element ต่างๆ ในฟอร์มสำหรับการ Validation และ Submit
    const registerForm = document.querySelector('form[action="register_process.php"]'); 

    if (registerForm) {
        const firstNameInput = document.getElementById('reg-firstname');
        const firstNameError = document.getElementById('reg-firstname-error');
        const lastNameInput = document.getElementById('reg-lastname');
        const lastNameError = document.getElementById('reg-lastname-error');
        const emailInput = document.getElementById('reg-email');
        const emailError = document.getElementById('reg-email-error');
        const telInput = document.getElementById('reg-tel');
        const telError = document.getElementById('reg-tel-error');
        const passwordInput = document.getElementById('reg-password');
        const passwordError = document.getElementById('reg-password-error');
        const confirmPasswordInput = document.getElementById('reg-confirm-password');
        const confirmPasswordError = document.getElementById('reg-confirm-password-error');
        const foodTypeCheckboxes = document.querySelectorAll('input[name="food_type[]"]'); // ใช้ตัวแปรคนละชื่อกับด้านบนได้เพื่อความชัดเจน
        const foodTypeError = document.getElementById('reg-foodtype-error');
        const agreeTermsCheckbox = document.getElementById('agreeTerms');
        const agreeTermsErrorMessage = document.getElementById('agreeTermsErrorMessage');

        // ฟังก์ชันสำหรับแสดง/ซ่อนข้อความ Error และจัดการ class ของ input
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

        function showCheckboxError(errorElement, message) {
            errorElement.textContent = message;
            errorElement.classList.add('show-error');
        }

        function hideCheckboxError(errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('show-error');
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidTel(tel) {
            const telRegex = /^\d{10}$/; 
            return telRegex.test(tel);
        }
        
        // ฟังก์ชัน validateForm หลัก (ถ้าต้องการแยกออกมา)
        function validateForm() {
            let isValidForm = true; 

            if (firstNameInput.value.trim() === '') {
                showError(firstNameInput, firstNameError, 'กรุณากรอกชื่อจริงของคุณ');
                isValidForm = false;
            } else {
                hideError(firstNameInput, firstNameError);
            }

            if (lastNameInput.value.trim() === '') {
                showError(lastNameInput, lastNameError, 'กรุณากรอกนามสกุลของคุณ');
                isValidForm = false;
            } else {
                hideError(lastNameInput, lastNameError);
            }

            if (emailInput.value.trim() === '') {
                showError(emailInput, emailError, 'กรุณากรอกอีเมล');
                isValidForm = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                showError(emailInput, emailError, 'รูปแบบอีเมลไม่ถูกต้อง');
                isValidForm = false;
            } else {
                hideError(emailInput, emailError);
            }

            const tel = telInput.value.trim();
            if (tel !== '' && !isValidTel(tel)) { 
                showError(telInput, telError, 'เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก');
                isValidForm = false;
            } else {
                hideError(telInput, telError);
            }

            if (passwordInput.value.trim().length < 8) {
                showError(passwordInput, passwordError, 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร');
                isValidForm = false;
            } else {
                hideError(passwordInput, passwordError);
            }

            if (confirmPasswordInput.value.trim() === '') {
                showError(confirmPasswordInput, confirmPasswordError, 'กรุณายืนยันรหัสผ่าน');
                isValidForm = false;
            } else if (confirmPasswordInput.value.trim() !== passwordInput.value.trim()) {
                showError(confirmPasswordInput, confirmPasswordError, 'รหัสผ่านไม่ตรงกัน');
                isValidForm = false;
            } else {
                hideError(confirmPasswordInput, confirmPasswordError);
            }

            const checkedFoodTypes = Array.from(foodTypeCheckboxes).some(checkbox => checkbox.checked);
            if (!checkedFoodTypes) {
                showCheckboxError(foodTypeError, 'กรุณาเลือกอย่างน้อย 1 ประเภทอาหารที่คุณชอบ');
                isValidForm = false;
            } else {
                hideCheckboxError(foodTypeError);
            }

            if (!agreeTermsCheckbox.checked) {
                showCheckboxError(agreeTermsErrorMessage, 'กรุณายินยอมข้อตกลงและเงื่อนไขก่อนดำเนินการต่อ');
                isValidForm = false;
            } else {
                hideCheckboxError(agreeTermsErrorMessage);
            }
            return isValidForm;
        }


        // --- Event Listener สำหรับ Form Submit หลัก ---
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault(); 

            // เรียกใช้ validation ก่อนส่งข้อมูล
            if (!validateForm()) { 
                return; // ถ้าฟอร์มไม่ถูกต้อง ให้หยุด
            }

            // ถ้าฟอร์มถูกต้อง ให้เริ่มกระบวนการ fetch
            const formData = new FormData(registerForm);

            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('Non-JSON response from register_process.php:', text);
                        throw new Error('Server did not return valid JSON. Check PHP errors.');
                    });
                }
            })
            .then(data => {
                  if (data.success) {
                    const userEmail = data.email; // อีเมลจริงจาก response ของ PHP (จาก register_process.php)
                    const userName = data.username || data.firstname; 
                    const verificationCode = data.verification_code; 

                    sendEmailViaEmailJS(userEmail, 'verification', {
                        userName: userName,
                        verificationCode: verificationCode 
                    })
                    .then(() => {
                        alert('ลงทะเบียนสำเร็จ! กรุณาตรวจสอบอีเมลเพื่อยืนยันบัญชี');
                        // ** สำคัญ: แนบอีเมลไปใน URL เมื่อ Redirect **
                        window.location.href = 'verify_email.php?email=' + encodeURIComponent(userEmail); 
                    })
                    .catch(emailError => {
                        alert('ลงทะเบียนสำเร็จ แต่ไม่สามารถส่งอีเมลยืนยันได้: ' + emailError.message);
                        console.error('Verification email send failed:', emailError);
                    });
                    
                } else {
                    alert('ลงทะเบียนไม่สำเร็จ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error during registration fetch:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
            });
        });

        // สำหรับการล้าง error เมื่อผู้ใช้เริ่มพิมพ์หรือเลือก ---
        firstNameInput.addEventListener('input', () => hideError(firstNameInput, firstNameError));
        lastNameInput.addEventListener('input', () => hideError(lastNameInput, lastNameError));
        emailInput.addEventListener('input', () => hideError(emailInput, emailError));
        telInput.addEventListener('input', () => {
            const tel = telInput.value.trim();
            if (tel === '' || isValidTel(tel)) { 
                hideError(telInput, telError);
            }
        });
        passwordInput.addEventListener('input', () => hideError(passwordInput, passwordError));
        confirmPasswordInput.addEventListener('input', () => {
            hideError(confirmPasswordInput, confirmPasswordError);
            if (confirmPasswordInput.value.trim() === passwordInput.value.trim()) {
                hideError(confirmPasswordInput, confirmPasswordError);
            }
        });

        foodTypeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedFoodTypes = Array.from(foodTypeCheckboxes).some(cb => cb.checked);
                if (checkedFoodTypes) {
                    hideCheckboxError(foodTypeError);
                }
            });
        });

        agreeTermsCheckbox.addEventListener('change', () => {
            if (agreeTermsCheckbox.checked) {
                hideCheckboxError(agreeTermsErrorMessage);
            }
        });

        window.goBack = function() {
            window.history.back();
        };
    } // สิ้นสุด if (registerForm)
}); // สิ้นสุด DOMContentLoaded