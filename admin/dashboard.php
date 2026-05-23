<?php

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

try {
    // Tổng số người dùng của web
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    $total_users = $result->fetch()['total'];
    
    // Tổng số phòng trọ
    $sql = "SELECT COUNT(*) as total FROM motel";
    $result = $conn->query($sql);
    $total_motels = $result->fetch()['total'];
    
    // Phòng chờ duyệt
    $sql = "SELECT COUNT(*) as total FROM motel WHERE approve = 0";
    $result = $conn->query($sql);
    $pending_motels = $result->fetch()['total'];
    
    // Phòng được duyệt
    $sql = "SELECT COUNT(*) as total FROM motel WHERE approve = 1";
    $result = $conn->query($sql);
    $approved_motels = $result->fetch()['total'];
    
    // Lấy danh sách phòng chờ duyệt
    $sql = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name
            FROM motel m
            JOIN users u ON m.user_id = u.id
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            WHERE m.approve = 0
            ORDER BY m.created_at DESC
            LIMIT 10";
    $result = $conn->query($sql);
    $pending_list = $result->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Sidebar Menu -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group sticky-top" style="top: 20px;">
                <a href="dashboard.php" class="list-group-item list-group-item-action active bg-primary text-white">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="manage_motels.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-house"></i> Quản lý phòng trọ
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people"></i> Quản lý người dùng
                </a>
                <a href="statistics.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-bar-chart"></i> Thống kê
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="fw-bold mb-4"><i class="bi bi-speedometer2"></i> Bảng điều khiển</h2>
            
            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0">Tổng người dùng</p>
                                    <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                                </div>
                                <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-success text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0">Tổng phòng trọ</p>
                                    <h3 class="fw-bold"><?php echo $total_motels; ?></h3>
                                </div>
                                <i class="bi bi-house-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-warning text-dark shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0">Chờ duyệt</p>
                                    <h3 class="fw-bold"><?php echo $pending_motels; ?></h3>
                                </div>
                                <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-info text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0">Đã duyệt</p>
                                    <h3 class="fw-bold"><?php echo $approved_motels; ?></h3>
                                </div>
                                <i class="bi bi-check-circle-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách phòng chờ duyệt -->
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-warning text-dark" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-hourglass-split"></i> Phòng chờ duyệt (<?php echo count($pending_list); ?>)</h5>
                </div>
                
                <div class="card-body">
                    <?php if (count($pending_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên phòng</th>
                                        <th>Chủ</th>
                                        <th>Giá</th>
                                        <th>Ngày đăng</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_list as $motel): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars(substr($motel['title'], 0, 40)); ?></strong></td>
                                            <td><?php echo htmlspecialchars($motel['user_name']); ?></td>
                                            <td><?php echo number_format($motel['price']); ?> đ</td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($motel['created_at'])); ?></td>
                                            <td>
                                                <a href="approve_motel.php?id=<?php echo $motel['ID']; ?>&action=approve" class="btn btn-sm btn-success" title="Duyệt">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <a href="approve_motel.php?id=<?php echo $motel['ID']; ?>&action=reject" class="btn btn-sm btn-danger" title="Từ chối">
                                                    <i class="bi bi-x"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <a href="manage_motels.php" class="btn btn-warning btn-sm">
                            <i class="bi bi-list"></i> Xem tất cả
                        </a>
                    <?php else: ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle"></i> Tất cả bài đăng đã được duyệt!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
