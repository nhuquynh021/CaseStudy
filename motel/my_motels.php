<?php
// Danh sách phòng trọ của người dùng

include '../config/connect.php';

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /Case_Study/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    // Lấy danh sách phòng trọ của người dùng
    $sql = "SELECT m.*, d.name as district_name, c.name as category_name
            FROM motel m
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            WHERE m.user_id = ?
            ORDER BY m.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $motels = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Xử lý xóa phòng trọ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $motel_id = (int)$_POST['motel_id'];
    
    try {
        // Kiểm tra phòng trọ có thuộc về người dùng không
        $sql_check = "SELECT id FROM motel WHERE id = ? AND user_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$motel_id, $user_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $sql_delete = "DELETE FROM motel WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->execute([$motel_id]);
            
            $success = 'Xóa phòng trọ thành công!';
            
            // Lấy lại danh sách
            $sql = "SELECT m.*, d.name as district_name, c.name as category_name
                    FROM motel m
                    JOIN districts d ON m.district_id = d.id
                    JOIN category c ON m.category_id = c.id
                    WHERE m.user_id = ?
                    ORDER BY m.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            $motels = $stmt->fetchAll();
        } else {
            $error = 'Phòng trọ không tồn tại!';
        }
    } catch (PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list"></i> Phòng trọ của tôi</h5>
                    <a href="add_motel.php" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Thêm phòng mới
                    </a>
                </div>
                
                <div class="card-body">
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
                    
                    <?php if (count($motels) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ảnh</th>
                                        <th>Tên phòng</th>
                                        <th>Giá</th>
                                        <th>Diện tích</th>
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
                                            <td>
                                                <?php if (!empty($motel['images']) && file_exists('../uploads/' . $motel['images'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                                        <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars(substr($motel['title'], 0, 30)); ?></strong></td>
                                            <td><?php echo number_format($motel['price']); ?> đ</td>
                                            <td><?php echo number_format($motel['area']); ?> m²</td>
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
                                                <a href="detail.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-info" title="Xem">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit_motel.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
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
                            <i class="bi bi-info-circle"></i> Bạn chưa đăng phòng trọ nào. <a href="add_motel.php">Đăng phòng mới</a>
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
                <p>Bạn có chắc chắn muốn xóa phòng trọ này? Hành động này không thể hoàn tác.</p>
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
// Hàm xác nhận xóa
function confirmDelete(motelId) {
    document.getElementById('deleteMotelId').value = motelId;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>
