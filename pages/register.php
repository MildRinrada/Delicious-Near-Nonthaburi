<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/register.css?v=<?php echo time(); ?>">
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="container">
        <div class="header-section">
            <h1 class="RegisterTitle">ลงทะเบียน</h1>
            <a href="#" onclick="goBack(); return false;" class="haveAccount">
                <h4>มีบัญชีอยู่แล้ว?</h4>
            </a>
        </div>

        <form class="register-form" action="register_process.php" method="POST" novalidate>

            <div class="form-row">
                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-firstname" class="RegisterLabel">ชื่อจริง <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="reg-firstname-error">กรุณากรอกชื่อจริงของคุณ</span>
                    </div>
                    <input type="text" id="reg-firstname" name="reg_firstname" placeholder="พิมพ์ชื่อจริงของคุณ..." required autocomplete="given-name">
                </div>

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-lastname" class="RegisterLabel">นามสกุล <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="reg-lastname-error">กรุณากรอกนามสกุลของคุณ</span>
                    </div>
                    <input type="text" id="reg-lastname" name="reg_lastname" placeholder="พิมพ์นามสกุลของคุณ..." required autocomplete="family-name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-email" class="RegisterLabel">อีเมล <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="reg-email-error">กรุณากรอกอีเมลที่ถูกต้อง</span>
                    </div>
                    <input type="email" id="reg-email" name="reg_email" placeholder="พิมพ์อีเมลของคุณ..." required autocomplete="email">
                </div>

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-tel" class="RegisterLabel">เบอร์โทรศัพท์ (สำหรับโปรโมชัน)</label>
                        <span class="error-message" id="reg-tel-error">กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง (ไม่บังคับ)</span>
                    </div>
                    <input type="tel" id="reg-tel" name="reg_tel" placeholder="พิมพ์เบอร์โทรศัพท์ของคุณ..." autocomplete="tel">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-password" class="RegisterLabel">รหัสผ่าน <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="reg-password-error">กรุณากรอกรหัสผ่าน (6 ตัวขึ้นไป)</span>
                    </div>
                    <input type="password" id="reg-password" name="reg_password" placeholder="รหัสผ่าน..." required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="reg-confirm-password" class="RegisterLabel">ยืนยันรหัสผ่าน <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="reg-confirm-password-error">รหัสผ่านไม่ตรงกัน</span>
                    </div>
                    <input type="password" id="reg-confirm-password" name="reg_confirm_password" placeholder="ยืนยันรหัสผ่าน..." required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <div class="label-and-error-row">
                    <label class="RegisterLabel">ประเภทอาหารที่ชอบ (เลือกได้หลายข้อ) <span class="required-asterisk">*</span></label>
                    <span class="error-message" id="reg-foodtype-error">กรุณาเลือกอย่างน้อย 1 ประเภท</span>
                </div>
                <div class="checkbox-group">
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="1"> 
                        <span class="checkbox-text">อาหารจานเดียว</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="2">
                        <span class="checkbox-text">อาหารตามสั่ง</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="3">
                        <span class="checkbox-text">ก๋วยเตี๋ยว</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="4">
                        <span class="checkbox-text">อาหารอีสาน</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="5">
                        <span class="checkbox-text">อาหารทะเล</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="6">
                        <span class="checkbox-text">อาหารญี่ปุ่น</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="7">
                        <span class="checkbox-text">อาหารตะวันตก</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="8">
                        <span class="checkbox-text">อาหารปักษ์ใต้</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="9">
                        <span class="checkbox-text">อาหารจีน</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="10">
                        <span class="checkbox-text">ของหวาน</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="11">
                        <span class="checkbox-text">อาหารริมทาง</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="12">
                        <span class="checkbox-text">เครื่องดื่ม</span>
                    </label>
                    <label class="custom-checkbox-large">
                        <input type="checkbox" name="food_type[]" value="13">
                        <span class="checkbox-text">อื่นๆ</span>
                    </label>
                </div>
            </div>

            <div class="agreeTerms-container">
                <label for="agreeTerms" class="custom-checkbox-label">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required class="custom-checkbox-input">
                    <span class="checkmark"></span>
                    ฉันยินยอมตาม<a href="terms.html" target="_blank">ข้อตกลงและเงื่อนไข</a>การให้บริการ <span class="required-asterisk">*</span>
                </label>
                <span id="agreeTermsErrorMessage" class="error-message" style="color: red; display: block; font-size: 0.9em;"></span>
            </div>

            <div class="form-group">
                <button type="submit" class="button1">ลงทะเบียน</button>
            </div>
        </form>
    </div>

    <br>

    <?php include('components/footer.html'); ?>

    <script type="text/javascript" src="https://cdn.emailjs.com/sdk/2.3.2/email.min.js"></script>
    <script type="text/javascript">
        (function(){
            emailjs.init("QOtzQR-DwANTtEMz9");
        })();
    </script>
    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/register.js?v=<?php echo time(); ?>"></script>
</body>
</html>