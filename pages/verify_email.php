<?php
// ดึงค่าอีเมลจาก Query String
$display_email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'your.email@example.com';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันอีเมล - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/verify_email.css?v=<?php echo time(); ?>"> 
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    
    <script type="text/javascript">
        (function(){
           emailjs.init({
             publicKey: "YOUR_PUBLIC_KEY", // <--- **ใส่ Public Key ของคุณตรงนี้! (ตัวเดียวกับที่ใช้ใน forgot_pass.php)**
           });
        })();
    </script>
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="container">
        <div class="verify-email-section">
            <h1>ยืนยันอีเมลของคุณ</h1>
            <p>
                เราได้ส่งรหัสยืนยันไปยังอีเมล **<span id="user-email-display"><?php echo $display_email; ?></span>**<br>
                กรุณากรอกรหัสที่ได้รับเพื่อยืนยันบัญชีของคุณ
            </p>

            <form class="verify-email-form" action="verify_email_process.php" method="POST" novalidate>
                <input type="hidden" name="email" id="emailToVerify" value="<?php echo htmlspecialchars($display_email); ?>">

                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="verification-code" class="RegisterLabel">รหัสยืนยัน <span class="required-asterisk">*</span></label>
                        <span class="error-message" id="code-error"></span> 
                    </div>
                    <input type="text" id="verification-code" name="verification_code" placeholder="ป้อนรหัสยืนยัน 6 หลัก" required maxlength="6" pattern="\d{6}">
                </div>

                <div class="button-group">
                    <button type="submit" class="button1 primary">ยืนยันอีเมล</button>
                    <button type="button" class="button1 secondary" onclick="window.location.href='login.php';">กลับไปหน้าเข้าสู่ระบบ</button>
                    <button type="button" class="button1 tertiary" onclick="window.location.href='change_email.php?old_email=<?php echo urlencode($display_email); ?>';">อีเมลผิด?</button>
                </div>

                <div class="link-group">
                    <a href="#" id="resend-code-link" class="resend-link">ไม่ได้รับรหัส? ส่งรหัสอีกครั้ง</a>
                </div>
            </form>
        </div>
    </div>

    <br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/verify_email.js?v=<?php echo time(); ?>"></script> 
</body>
</html>