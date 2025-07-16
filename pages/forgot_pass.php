<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" href="../css/forgot_pass.css?v=<?php echo time(); ?>"> 
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    
    <script type="text/javascript">
        (function(){
           emailjs.init({
             publicKey: "YOUR_PUBLIC_KEY", // <--- **คุณต้องเปลี่ยนเป็น Public Key ของ EmailJS ของคุณ**
           });
        })();
    </script>
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>
    
    <div class="container">
        <div class="right-column">
            <a href="#" onclick="goBack(); return false;" class="cross-icon-wrapper"> 
                <img src="../static/images/icon/cross.svg" class="cross_icon" alt="ปิด">
            </a>
            <h1 class="LoginTitle">ลืมรหัสผ่าน?</h1>
            <p class="subtitle">กรุณากรอกอีเมลที่ลงทะเบียนไว้ เราจะส่งลิงก์สำหรับรีเซ็ตรหัสผ่านไปให้คุณ</p>

            <form class="forgot-password-form" method="post" action="forgot_pass_process.php" novalidate>
                <div class="form-group">
                    <div class="label-and-error-row">
                        <label for="email" class="LoginLabel">อีเมล</label>
                        <span class="error-message" id="email-error">กรุณากรอกอีเมล</span>
                    </div>
                    <input type="email" id="email" name="email" placeholder="พิมพ์อีเมลของคุณ..." required>
                </div>

                <div class="form-bottom-actions"> 
                    <input type="submit" value="ส่งลิงก์รีเซ็ต" class="button1">
                </div>
            </form>

            <div class="divider-with-text">
                <span>หรือ</span>
            </div>

            <div class="register-link-container">
                <p>กลับไปหน้า <a href="login.php" class="register-link">เข้าสู่ระบบ</a></p>
            </div>
        </div>
    </div>

    <br><br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/forgot_pass.js?v=<?php echo time(); ?>"></script> 
</body>
</html>