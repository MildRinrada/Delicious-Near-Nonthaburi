<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

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
    .user-profile-menu {
        position: relative;
        display: inline-block;
        padding-top: 5px;
        padding-bottom: 5px;
        margin: -5px 0; 
    }

    .user-profile-menu .profile-link .user-name {
        border-right: 1px solid #ccc; 
        padding-right: 10px; 
        margin-right: 20px; 
        line-height: 1;
        border-right: 2px solid #4F2B14; 
    }

    .user-profile-menu .profile-link {
        display: flex;
        align-items: center;
        gap: 0; 
        padding: 10px 15px; 
        border-radius: 5px;
        background-color: #F8C44E;
        color: #333;
        transition: background-color 0.3s ease;
    }

    .user-profile-menu .profile-link:hover {
        background-color: #e0b040; 
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
        margin-top: 0px; 
        border-radius: 5px;
        right: 0;
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

    .user-profile-menu:hover .dropdown-menu {
        display: block;
    }
</style>