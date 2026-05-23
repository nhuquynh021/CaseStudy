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
    // Lấy danh sách tất cả người dùng
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    $users = $result->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Đoạn ni để xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = (int)$_POST['user_id'];
    
    // Không cho xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Không thể xóa chính bạn!';
    } else {
        try {
            // Xóa các phòng trọ của người dùng trước
            $sql_delete_motels = "DELETE FROM motel WHERE user_id = ?";
            $stmt_delete_motels = $conn->prepare($sql_delete_motels);
            $stmt_delete_motels->execute([$user_id]);
            
            // Xóa người dùng
            $sql_delete = "DELETE FROM users WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->execute([$user_id]);
            
            $success = 'Xóa người dùng thành công!';
            
            // Lấy lại danh sách
            $sql = "SELECT * FROM users";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
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
                <a href="manage_motels.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-house"></i> Quản lý phòng trọ
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action active bg-primary text-white">
                    <i class="bi bi-people"></i> Quản lý người dùng
                </a>
                <a href="statistics.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-bar-chart"></i> Thống kê
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="fw-bold mb-4"><i class="bi bi-people"></i> Quản lý người dùng</h2>
            
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
                    <?php if (count($users) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ tên</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Vai trò</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['ID']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($user['Name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['Phone'] ?? ''); ?></td>
                                            <td>
                                                <?php if ($user['Role'] == 1): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Người dùng</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                    <?php if ($user['ID'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display:inline;" 
                                        onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này? Tất cả bài đăng của họ cũng sẽ bị xóa!')">
    
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['ID'] ?>">
    
                                        <button type="submit" class="btn btn-danger btn-sm">
                                             Xóa
                                        </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">(Bạn)</span>
                                    <?php endif; ?>
                                </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> Không có người dùng nào
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Bạn có chắc muốn xóa người dùng này?  
                <br>
                <strong>Tất cả bài đăng phòng trọ của họ cũng sẽ bị xóa.</strong>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>

                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteUserId">

                    <button type="submit" class="btn btn-danger">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- <script>
function confirmDelete(userId) {
    document.getElementById('deleteUserId').value = userId;
}
</script> -->

<?php include '../includes/footer.php'; ?>