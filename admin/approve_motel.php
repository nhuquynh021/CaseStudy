<?php
// Trang ni để duyệt bài đăng phòng trọ của người dùng

include '../config/connect.php';

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: /Case_Study/auth/login.php");
    exit();
}

$motel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($motel_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    header("Location: dashboard.php");
    exit();
}


try {
    if ($action === 'approve') {
        $sql = "UPDATE motel SET approve = 1 WHERE id = ?";
    } else {
        $sql = "UPDATE motel SET approve = -1 WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$motel_id]);
    
    $message = $action === 'approve' ? 'Duyệt phòng trọ thành công!' : 'Từ chối phòng trọ thành công!';
    header("Location: dashboard.php?success=" . urlencode($message));
    exit();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
