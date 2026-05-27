<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'pharmacy_db';
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function checkAccess($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error_message'] = "Bạn không có quyền truy cập chức năng này.";
        header("Location: index.php");
        exit;
    }
}
?>