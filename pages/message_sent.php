<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งอีเมลสำเร็จ - อร่อยใกล้นนท์</title>
    <link rel="stylesheet" href="../css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/login.css"> <style>
        .message-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .message-container h1 {
            color: #4CAF50; /* สีเขียวสำหรับความสำเร็จ */
            margin-bottom: 20px;
        }
        .message-container p {
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
            margin-bottom: 25px;
        }
        .back-to-login {
            display: inline-block;
            padding: 10px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-to-login:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div id="header-container"><?php include('components/header.html'); ?></div>

    <div class="message-container">
        <h1>ส่งอีเมลสำเร็จ!</h1>
        <p>เราได้ส่งลิงก์สำหรับรีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว กรุณาตรวจสอบกล่องจดหมายของคุณ (รวมถึงโฟลเดอร์ Spam/Junk ด้วยนะคะ/ครับ)</p>
        <p>ลิงก์จะหมดอายุใน 1 ชั่วโมง</p>
        <a href="login.php" class="back-to-login">กลับไปหน้าเข้าสู่ระบบ</a>
    </div>

    <br><br>

    <?php include('components/footer.html'); ?>
    <script src="../js/common.js?v=<?php echo time(); ?>"></script>
</body>
</html>