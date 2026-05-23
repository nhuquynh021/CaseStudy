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

$error = '';
$success = '';

try {
    // Lấy danh sách tất cả phòng trọ
    $sql = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name
            FROM motel m
            JOIN users u ON m.user_id = u.id
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            ORDER BY m.created_at DESC";
    $result = $conn->query($sql);
    $motels = $result->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Đoạn ni là xóa phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $motel_id = (int)$_POST['motel_id'];
    
    try {
        $sql_delete = "DELETE FROM motel WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute([$motel_id]);
        
        $success = 'Xóa phòng trọ thành công!';
        
        // Lấy lại danh sách sau khi xóa
        $sql = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name
                FROM motel m
                JOIN users u ON m.user_id = u.id
                JOIN districts d ON m.district_id = d.id
                JOIN category c ON m.category_id = c.id
                ORDER BY m.created_at DESC";
        $result = $conn->query($sql);
        $motels = $result->fetchAll();
    } catch (PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
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
                <a href="manage_motels.php" class="list-group-item list-group-item-action active bg-primary text-white">
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
            <h2 class="fw-bold mb-4"><i class="bi bi-house"></i> Quản lý phòng trọ</h2>
            
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
            
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <?php if (count($motels) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên phòng</th>
                                        <th>Chủ</th>
                                        <th>Giá</th>
                                        <th>Khu vực</th>
                                        <th>Trạng thái</th>
                                        <th>Lượt xem</th>
                                        <th>Ngày đăng</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($motels as $motel): ?>
                                        <tr>
                                            <td><?php echo $motel['ID']; ?></td>
                                            <td><strong><?php echo htmlspecialchars(substr($motel['title'], 0, 30)); ?></strong></td>
                                            <td><?php echo htmlspecialchars($motel['user_name']); ?></td>
                                            <td><?php echo number_format($motel['price']); ?> đ</td>
                                            <td><?php echo htmlspecialchars($motel['district_name']); ?></td>
                                            <td>
                                                <?php if ($motel['approve'] == 1): ?>
                                                    <span class="badge bg-success">Đã duyệt</span>
                                                <?php elseif ($motel['approve'] == 0): ?>
                                                    <span class="badge bg-warning">Chờ duyệt</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Bị từ chối</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $motel['count_view']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($motel['created_at'])); ?></td>
                                            <td>
                                                <a href="/Case_Study/motel/detail.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-info" title="Xem">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($motel['approve'] != 1): ?>
                                                    <a href="approve_motel.php?id=<?php echo $motel['ID']; ?>&action=approve" class="btn btn-sm btn-success" title="Duyệt">
                                                        <i class="bi bi-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa" onclick="confirmDelete(<?php echo $motel['ID']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> Không có phòng trọ nào
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa phòng trọ này?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="motel_id" id="deleteMotelId" value="">
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(motelId) {
    document.getElementById('deleteMotelId').value = motelId;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>
