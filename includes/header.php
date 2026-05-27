<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống hỗ trợ gợi ý thuốc - Nhà Thuốc Thông Minh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-house-chimney-medical"></i>
            <span>SmartPharmacy</span>
        </a>
        
        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fa-solid fa-magnifying-glass"></i> Tra cứu gợi ý</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pharmacist'): ?>
                    <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="manage_medicines.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_medicines.php' ? 'active' : ''; ?>"><i class="fa-solid fa-pills"></i> Quản lý thuốc</a></li>
                    <li><a href="manage_symptoms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_symptoms.php' ? 'active' : ''; ?>"><i class="fa-solid fa-notes-medical"></i> Triệu chứng & Bệnh</a></li>
                <?php endif; ?>
                
                <li class="nav-user">
                    <i class="fa-regular fa-user"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="role-badge <?php echo $_SESSION['role']; ?>">
                        <?php 
                        if ($_SESSION['role'] === 'admin') echo 'Quản trị viên';
                        elseif ($_SESSION['role'] === 'pharmacist') echo 'Dược sĩ';
                        else echo 'Khách hàng';
                        ?>
                    </span>
                </li>
                <li><a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a></li>
                <li><a href="register.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>"><i class="fa-solid fa-user-plus"></i> Đăng ký</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>
<main>
    
<?php
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
?>

