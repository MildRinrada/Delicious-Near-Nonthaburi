<?php
// components/dynamic_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// แก้ไขตรงนี้: ตรวจสอบให้แน่ใจว่าคีย์ 'user_firstname' และ 'user_surname' มีอยู่ก่อนนำไปใช้
// ใช้ Null Coalescing Operator (??) เพื่อกำหนดค่าเริ่มต้นเป็นสตริงว่าง ถ้าคีย์ไม่มีอยู่
$user_firstname = $is_logged_in ? htmlspecialchars($_SESSION['user_firstname'] ?? '') : '';
$user_surname = $is_logged_in ? htmlspecialchars($_SESSION['user_surname'] ?? '') : '';
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email'] ?? '') : '';
?>
<header>
    <a href="index.php"><img src="../static/images/อร่อยใกล้นนท์_logo.png" alt="โลโก้ อร่อยใกล้นนท์" id="main-logo"></a>
    <nav>
        <ul>
            <li><a href="index.php" class="nav1_header">หน้าหลัก</a></li>
            <li><a href="index.php/#contact" class="nav1_header smooth-scroll">ติดต่อ</a></li>
            <?php if ($is_logged_in): ?>
                <li class="user-profile-menu">
                    <a href="#" class="nav1_header profile-link">
                        <span class="user-name"><?php echo $user_firstname; ?> <?php echo $user_surname; ?></span>
                        <img src="../static/images/profile.png" alt="โปรไฟล์" class="profile-icon">
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">ข้อมูลส่วนตัว</a></li>
                        <li><a href="settings.php">ตั้งค่า</a></li>
                        <li><a href="logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="login.php"><button class="login-button">เข้าสู่ระบบ</button></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<style>
/* CSS สำหรับเมนูโปรไฟล์ผู้ใช้ */
/* CSS สำหรับเมนูโปรไฟล์ผู้ใช้ */
.user-profile-menu {
    position: relative;
    display: inline-block;
    /* เพิ่ม padding-top และ padding-bottom ตรงนี้ */
    padding-top: 5px; /* เพิ่มระยะห่างด้านบนให้ dropdown เลื่อนขึ้นมาอยู่บนพื้นที่ hover */
    padding-bottom: 5px; /* เพิ่มระยะห่างด้านล่างให้ dropdown เลื่อนลงมาอยู่บนพื้นที่ hover */
    margin: -5px 0; /* อาจจะต้องปรับ margin-top/bottom เป็นค่าติดลบเพื่อชดเชย padding ที่เพิ่มเข้ามา */
}

.user-profile-menu .profile-link .user-name {
    /* เพิ่ม CSS ด้านล่างนี้ */
    border-right: 1px solid #ccc; /* ตัวอย่าง: เส้นสีเทาอ่อน หนา 1px */
    padding-right: 10px; /* เพิ่มระยะห่างระหว่างเส้นกับรูปโปรไฟล์ */
    margin-right: 20px;  /* (optional) เพิ่มระยะห่างทางขวาของเส้นอีกนิดถ้าต้องการ */
    line-height: 1; /* ช่วยให้เส้นไม่สูงเกินไป ถ้ามีปัญหา */
    border-right: 2px solid #4F2B14; /* ตัวอย่างสีเส้น */
}

.user-profile-menu .profile-link {
    display: flex;
    align-items: center;
    gap: 0; /* เปลี่ยน gap เป็น 0 เพื่อควบคุมระยะห่างด้วย padding/margin ของ user-name */
    padding: 10px 15px; 
    border-radius: 5px;
    background-color: #F8C44E;
    color: #333;
    transition: background-color 0.3s ease;
}

.user-profile-menu .profile-link:hover {
    background-color: #e0b040; /* แก้ไขสี hover ให้มีค่าที่ถูกต้อง */
}

.user-profile-menu .profile-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    transform: scale(1.5);
}

.user-profile-menu .dropdown-menu {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    list-style-type: none;
    padding: 0;
    /* ปรับ margin-top ให้เป็น 0 หรือค่าติดลบเล็กน้อย */
    margin-top: 0px; /* ทำให้เมนูแนบชิดกับลิงก์หลัก */
    border-radius: 5px;
    right: 0;
    /* เพิ่ม border-top เพื่อให้มีเส้นแบ่งระหว่างเมนูหลักกับ dropdown */
    border-top: 1px solid #eee; 
}

.user-profile-menu .dropdown-menu li a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    text-align: left;
}

.user-profile-menu .dropdown-menu li a:hover {
    background-color: #f1f1f1;
}

/* แสดง dropdown เมื่อ hover ที่เมนูหลัก */
.user-profile-menu:hover .dropdown-menu {
    display: block;
}
</style>