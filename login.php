<?php
require_once __DIR__ . '/includes/config.php';
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pharmacist') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success_message'] = "Đăng nhập thành công! Chào mừng " . htmlspecialchars($user['username']) . ".";
                if ($user['role'] === 'admin' || $user['role'] === 'pharmacist') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            }
        } catch (PDOException $e) {
            $error = 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-container">
    <div class="card">
        <div class="auth-header">
            <i class="fa-solid fa-lock-open" style="font-size: 3rem; color: var(--primary); margin-bottom: 12px;"></i>
            <h2>Đăng Nhập Hệ Thống</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 8px;">Dành cho Khách hàng, Dược sĩ & Admin</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Nhập tên đăng nhập của bạn..." required autocomplete="username">
            </div>
            <div class="form-group" style="margin-bottom: 30px;">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu..." required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-right-to-bracket"></i> ĐĂNG NHẬP
            </button>
        </form>
        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-secondary);">
            Chưa có tài khoản? <a href="register.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Đăng ký ngay</a>
        </div>
        
        <div style="border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 15px; font-size: 0.8rem; color: var(--text-secondary);">
       
            <ul style="margin-left: 20px; margin-top: 5px; list-style-type: square;">
                
            </ul>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
