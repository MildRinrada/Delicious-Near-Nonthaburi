<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - เว็บอร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>
    
    <div class="container">
        <div class="left-column"></div>
        <div class="right-column">
            <a href="#" onclick="goBack(); return false;" class="cross-icon-wrapper"> 
                <img src="../static/images/icon/cross.svg" class="cross_icon" alt="ปิด">
            </a>
            <h1 class="LoginTitle">เข้าสู่ระบบ</h1>
            <form class="login-form" method="post" action="login_process.php" novalidate>
            <div class="form-group">
                <div class="label-and-error-row">
                    <label for="email" class="LoginLabel">อีเมล</label>
                    <span class="error-message" id="email-error">กรุณากรอกอีเมล</span>
                </div>
                <input type="email" id="email" name="email" placeholder="พิมพ์อีเมลของคุณ..." required>
            </div>

            <div class="form-group">
                <div class="label-and-error-row">
                    <label for="password" class="LoginLabel">รหัสผ่าน</label>
                    <span class="error-message" id="password-error">กรุณากรอกรหัสผ่าน</span>
                </div>
                <input type="password" id="password" name="password" placeholder="รหัสผ่าน..." required>
                <a href="forgot_pass.php" class="forgot-password-link">ลืมรหัสผ่าน?</a>
            </div>


                <div class="form-bottom-actions"> 
                    <div class="remember-me-container">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span class="checkmark"></span>
                            <p class="rememberLabel">จดจำรหัสผ่าน</p>
                        </label>
                    </div>
                    <input type="submit" value="เข้าสู่ระบบ" class="button1">
                </div>
            </form>

            <div class="divider-with-text">
                <span>หรือ</span>
            </div>

            <div class="register-link-container">
                <p>เป็นสมาชิกของพวกเรา <a href="register.php" class="register-link">สมัครสมาชิก</a></p>
            </div>

        </div>
    </div>

    <br><br>

    <?php include('components/footer.html'); ?>

    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
    <script src="../js/login.js"></script>
</body>
</html>