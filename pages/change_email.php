<?php

// ดึงอีเมลเก่าจาก Query String เพื่อแสดงให้ผู้ใช้เห็น (ถ้ามี)
$old_email = isset($_GET['old_email']) ? htmlspecialchars($_GET['old_email']) : '';

// สำหรับแสดงข้อความ Error จากการประมวลผล (ถ้ามี)
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขอีเมล - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/change_email.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/verify_email.css?v=<?php echo time(); ?>"> 
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="container">
        <div class="change-email-section">
            <h1>แก้ไขอีเมลของคุณ</h1>
            <?php if ($error_message): ?>
                <p class="error-message show-error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <p>
                กรุณากรอกอีเมลใหม่ที่คุณต้องการใช้สำหรับบัญชีของคุณ
            </p>

            <form class="change-email-form" action="change_email_process.php" method="POST" novalidate>
                <input type="hidden" name="old_email" value="<?php echo htmlspecialchars($old_email); ?>">

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="new-email" class="RegisterLabel">อีเมลใหม่ <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="email-error">กรุณากรอกอีเมลที่ถูกต้อง</span>
                    </div>
                    <input type="email" id="new-email" name="new_email" placeholder="ป้อนอีเมลใหม่" required value="<?php echo $old_email; ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="button1 primary">บันทึกอีเมลใหม่</button>
                    <button type="button" class="button1 secondary" onclick="window.history.back();">ยกเลิก</button> 
                </div>
            </form>
        </div>
    </div>

    <br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js"></script>
    <script>
        // JavaScript สำหรับ client-side validation (ถ้าต้องการ)
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.change-email-form');
            const newEmailInput = document.getElementById('new-email');
            const emailError = document.getElementById('email-error');

            form.addEventListener('submit', function(event) {
                let isValid = true;

                // ตรวจสอบอีเมล
                if (!newEmailInput.value.trim()) {
                    emailError.textContent = 'กรุณากรอกอีเมล';
                    emailError.classList.add('show-error');
                    newEmailInput.classList.add('is-invalid');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmailInput.value)) { // Basic email regex
                    emailError.textContent = 'กรุณากรอกอีเมลที่ถูกต้อง';
                    emailError.classList.add('show-error');
                    newEmailInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    emailError.classList.remove('show-error');
                    newEmailInput.classList.remove('is-invalid');
                }

                if (!isValid) {
                    event.preventDefault(); // หยุดการ submit form ถ้ามี error
                }
            });

            // ซ่อน error message เมื่อผู้ใช้พิมพ์
            newEmailInput.addEventListener('input', function() {
                emailError.classList.remove('show-error');
                newEmailInput.classList.remove('is-invalid');
            });
        });
    </script>
</body>
</html>