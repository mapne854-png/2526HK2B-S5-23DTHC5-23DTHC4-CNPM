<?php
require_once __DIR__ . '/includes/config.php';
// Nếu người dùng đã đăng nhập, tự động chuyển hướng
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = 'customer'; // Mặc định tất cả đăng ký từ website đều là Khách hàng (customer). Quyền Dược sĩ và Admin đã có sẵn hoặc do admin cấp.
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ email không đúng định dạng.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải chứa ít nhất 6 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không trùng khớp.';
    } else {
        try {
            // Kiểm tra trùng tên đăng nhập hoặc email
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Tên đăng nhập hoặc địa chỉ email đã được sử dụng.';
            } else {
                // Mã hóa mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Thêm người dùng mới
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)");
                $insert_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password,
                    'email' => $email,
                    'role' => $role
                ]);
                $success = 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.';
                $_SESSION['success_message'] = $success;
                header("Location: login.php");
                exit;
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
            <i class="fa-solid fa-user-plus" style="font-size: 3rem; color: var(--secondary); margin-bottom: 12px;"></i>
            <h2>Đăng Ký Tài Khoản</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 8px;">Tạo tài khoản cá nhân của bạn</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Tên đăng nhập <span style="color: var(--danger);">*</span></label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Nhập tên đăng nhập (viết liền không dấu)..." required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Địa chỉ Email <span style="color: var(--danger);">*</span></label>
                <input type="email" name="email" id="email" class="form-control" placeholder="example@domain.com" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu <span style="color: var(--danger);">*</span></label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Tối thiểu 6 ký tự..." required autocomplete="new-password">
            </div>
            <div class="form-group" style="margin-bottom: 30px;">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span style="color: var(--danger);">*</span></label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu..." required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-secondary" style="width: 100%;">
                <i class="fa-solid fa-user-plus"></i> ĐĂNG KÝ NGAY
            </button>
        </form>
        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-secondary);">
            Đã có tài khoản? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Đăng nhập</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
