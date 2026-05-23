<?php
// Trang đăng nhập

include '../config/connect.php';

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã đăng nhập thì chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: /Case_Study/index.php");
    exit();
}

$error = '';
$success = '';
$show_recaptcha = false;

// Khởi tạo lần đăng nhập sai nếu chưa có
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Kiểm tra dữ liệu rỗng
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    }
    else {
        try {
            // Tìm user theo username
            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Kiểm tra mật khẩu
                if (password_verify($password, $user['Password'])) {
                    // Đăng nhập thành công
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['username'] = $user['Username'];
                    $_SESSION['role'] = $user['Role'];
                    $_SESSION['name'] = $user['Name'];
                    $_SESSION['login_attempts'] = 0;
                    if ($user['Role'] === 1) {
                        header("Location: /Case_Study/admin/dashboard.php");
                    } else {
                        header("Location: /Case_Study/index.php");
                    }
                    exit();
                }
                else {
                    // Mật khẩu sai
                    $_SESSION['login_attempts']++;
                    $error = 'Tên đăng nhập hoặc mật khẩu sai!';
                }
            }
            else {
                // Username không tồn tại
                $_SESSION['login_attempts']++;
                $error = 'Tên đăng nhập hoặc mật khẩu sai!';
            }
            
            // Nếu sai 3 lần thì bật reCAPTCHA
            if ($_SESSION['login_attempts'] >= 3) {
                $show_recaptcha = true;
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Sai quá 3 lần thì bập Capcha lên
if ($_SESSION['login_attempts'] >= 3) {
    $show_recaptcha = true;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - GTPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <?php if ($show_recaptcha): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        
        .login-container h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            width: 100%;
            color: white;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #764ba2;
            color: white;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .g-recaptcha {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Đăng nhập</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="Nhập tên đăng nhập"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    placeholder="Nhập mật khẩu"
                    required
                >
            </div>
            
            <?php if ($show_recaptcha): ?>
                <div class="g-recaptcha" data-sitekey="YOUR_RECAPTCHA_SITE_KEY"></div>
                <small class="text-muted d-block mb-3">
                    <i class="bi bi-info-circle"></i> 
                    Bạn đã nhập sai mật khẩu 3 lần. Vui lòng xác nhận reCAPTCHA.
                </small>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-login">Đăng nhập</button>
        </form>
        
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a>
        </div>
        
        <hr>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
