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

// Lấy tháng và năm để lọc (mặc định là tháng hiện tại)
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Kiểm tra tháng năm hợp lệ
if ($month < 1 || $month > 12) $month = date('m');
if ($year < 2000 || $year > date('Y') + 1) $year = date('Y');

try {
    // Thống kê phòng trọ theo tháng
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN approve = 1 THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approve = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approve = -1 THEN 1 ELSE 0 END) as rejected,
                SUM(price) as total_price,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price
            FROM motel 
            WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$month, $year]);
    $monthly_stats = $stmt->fetch();
    
    // Top 5 phòng trọ có lượt xem nhiều nhất
    $sql_top = "SELECT m.*, u.name as user_name 
                FROM motel m
                JOIN users u ON m.user_id = u.id
                WHERE m.approve = 1
                ORDER BY m.count_view DESC
                LIMIT 5";
    $result_top = $conn->query($sql_top);
    $top_motels = $result_top->fetchAll();
    
    // Thống kê người dùng
    $sql_users = "SELECT COUNT(*) as total FROM users";
    $result_users = $conn->query($sql_users);
    $user_stats = $result_users->fetch();
    
    // Doanh thu ước tính
    $sql_revenue = "SELECT SUM(price) as total_revenue FROM motel WHERE approve = 1";
    $result_revenue = $conn->query($sql_revenue);
    $revenue_stats = $result_revenue->fetch();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar Menu -->
        <div class="col-md-3 mb-4">
            <div class="list-group sticky-top" style="top: 20px;">
                <a href="dashboard.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="manage_motels.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-house"></i> Quản lý phòng trọ
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people"></i> Quản lý người dùng
                </a>
                <a href="statistics.php" class="list-group-item list-group-item-action active bg-primary text-white">
                    <i class="bi bi-bar-chart"></i> Thống kê
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="fw-bold mb-4"><i class="bi bi-bar-chart"></i> Thống kê và báo cáo</h2>
            
            <!-- Bộ lọc -->
            <div class="card shadow-sm mb-4" style="border-radius: 10px;">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tháng</label>
                            <select name="month" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                                        Tháng <?php echo $m; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Năm</label>
                            <select name="year" class="form-select">
                                <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Xem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Thống kê tháng -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <p class="mb-1">Tổng bài đăng (Tháng <?php echo $month; ?>/<?php echo $year; ?>)</p>
                            <h3 class="fw-bold"><?php echo $monthly_stats['total'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-success text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <p class="mb-1">Đã duyệt</p>
                            <h3 class="fw-bold"><?php echo $monthly_stats['approved'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-warning text-dark shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <p class="mb-1">Chờ duyệt</p>
                            <h3 class="fw-bold"><?php echo $monthly_stats['pending'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-danger text-white shadow-sm" style="border-radius: 10px;">
                        <div class="card-body">
                            <p class="mb-1">Bị từ chối</p>
                            <h3 class="fw-bold"><?php echo $monthly_stats['rejected'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê giá -->
            <div class="card shadow-sm mb-4" style="border-radius: 10px;">
                <div class="card-header bg-info text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cash-coin"></i> Thống kê giá (Tháng <?php echo $month; ?>/<?php echo $year; ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <p class="mb-1 text-muted">Tổng giá</p>
                            <h4 class="fw-bold text-primary">
                                <?php echo number_format($monthly_stats['total_price'] ?? 0); ?> đ
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-muted">Giá trung bình</p>
                            <h4 class="fw-bold text-success">
                                <?php echo number_format($monthly_stats['avg_price'] ?? 0); ?> đ
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-muted">Giá thấp nhất</p>
                            <h4 class="fw-bold text-warning">
                                <?php echo number_format($monthly_stats['min_price'] ?? 0); ?> đ
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-muted">Giá Cco nhất</p>
                            <h4 class="fw-bold text-danger">
                                <?php echo number_format($monthly_stats['max_price'] ?? 0); ?> đ
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top phòng xem nhiều -->
            <div class="card shadow-sm mb-4" style="border-radius: 10px;">
                <div class="card-header bg-dark text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-fire"></i> Top 5 phòng xem nhiều nhất</h5>
                </div>
                <div class="card-body">
                    <?php if (count($top_motels) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên phòng</th>
                                        <th>Chủ</th>
                                        <th>Giá</th>
                                        <th>Lượt xem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_motels as $motel): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars(substr($motel['title'], 0, 40)); ?></strong></td>
                                            <td><?php echo htmlspecialchars($motel['user_name']); ?></td>
                                            <td><?php echo number_format($motel['price']); ?> đ</td>
                                            <td><span class="badge bg-primary"><?php echo $motel['count_view']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Không có dữ liệu
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
