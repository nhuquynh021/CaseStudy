<?php
// Trang chủ - Hiển thị danh sách phòng trọ

include 'config/connect.php';

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Lấy trang hiện tại (phân trang)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$items_per_page = 9;
$offset = ($page - 1) * $items_per_page;

// Lấy danh sách phòng trọ đã được duyệt
try {
    // Tổng số phòng
    $sql_count = "SELECT COUNT(*) as total FROM motel WHERE approve = 1";
    $result_count = $conn->query($sql_count);
    $total_motels = $result_count->fetch()['total'];
    $total_pages = ceil($total_motels / $items_per_page);
    
    // Lấy phòng trọ của trang hiện tại
    $sql = "SELECT m.*, u.name as user_name, u.phone as user_phone, d.name as district_name, c.name as category_name
            FROM motel m
            JOIN users u ON m.user_id = u.id
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            WHERE m.approve = 1
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $items_per_page, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $motels = $stmt->fetchAll();
    
    // Phòng xem nhiều nhất
    $sql_top = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name
                FROM motel m
                JOIN users u ON m.user_id = u.id
                JOIN districts d ON m.district_id = d.id
                JOIN category c ON m.category_id = c.id
                WHERE m.approve = 1
                ORDER BY m.count_view DESC
                LIMIT 3";
    $result_top = $conn->query($sql_top);
    $top_motels = $result_top->fetchAll();
    
    // Phòng mới đăng
    $sql_new = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name
                FROM motel m
                JOIN users u ON m.user_id = u.id
                JOIN districts d ON m.district_id = d.id
                JOIN category c ON m.category_id = c.id
                WHERE m.approve = 1
                ORDER BY m.created_at DESC
                LIMIT 3";
    $result_new = $conn->query($sql_new);
    $new_motels = $result_new->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary text-white p-5 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h1 class="display-4 fw-bold mb-2">Tìm phòng trọ</h1>
                <p class="lead mb-4">Khám phá hàng ngàn phòng trọ với giá tốt trên toàn thành phố</p>
                
                <div class="row">
                    <div class="col-md-8">
                        <form method="GET" action="/Case_Study/motel/search.php" class="d-flex">
                            <input 
                                type="text" 
                                name="keyword" 
                                class="form-control me-2" 
                                placeholder="Tìm kiếm theo địa chỉ, khu vực..."
                                style="border-radius: 5px;"
                            >
                            <button class="btn btn-light fw-bold" type="submit" style="border-radius: 5px;">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Phòng xem nhiều nhất -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold mb-4">
                <i class="bi bi-fire text i-orange"></i> Phòng xem nhiều nhất
            </h2>
            <div class="row">
                <?php foreach ($top_motels as $motel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                            <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($motel['images']) && file_exists('uploads/' . $motel['images'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($motel['title']); ?></h5>
                                
                                <div class="mb-2">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($motel['category_name']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($motel['district_name']); ?></span>
                                </div>
                                
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($motel['address']); ?>
                                </p>
                                
                                <div class="row text-center text-muted small mb-3">
                                    <div class="col">
                                        <strong><?php echo number_format($motel['area']); ?></strong> m²
                                    </div>
                                    <div class="col">
                                        <strong><?php echo $motel['count_view']; ?></strong> lượt xem
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-danger fw-bold mb-0">
                                        <?php echo number_format($motel['price']); ?> đ
                                    </h5>
                                    <a href="./motel/detail.php?id=<?php echo $motel['ID']; ?>">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Phòng mới đăng -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold mb-4">
                <i class="bi bi-star" style="color: #ffc107;"></i> Phòng mới đăng
            </h2>
            <div class="row">
                <?php foreach ($new_motels as $motel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                            <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($motel['images']) && file_exists('uploads/' . $motel['images'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($motel['title']); ?></h5>
                                
                                <div class="mb-2">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($motel['category_name']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($motel['district_name']); ?></span>
                                </div>
                                
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($motel['address']); ?>
                                </p>
                                
                                <div class="row text-center text-muted small mb-3">
                                    <div class="col">
                                        <strong><?php echo number_format($motel['area']); ?></strong> m²
                                    </div>
                                    <div class="col">
                                        <strong><?php echo $motel['count_view']; ?></strong> lượt xem
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-danger fw-bold mb-0">
                                        <?php echo number_format($motel['price']); ?> đ
                                    </h5>
                                    <a href="/Case_Study/motel/detail.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Tất cả phòng trọ -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Tất cả phòng trọ</h2>
            
            <?php if (count($motels) > 0): ?>
                <div class="row">
                    <?php foreach ($motels as $motel): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden; transition: transform 0.3s;">
                                <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                    <?php if (!empty($motel['images']) && file_exists('uploads/' . $motel['images'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($motel['title']); ?></h5>
                                    
                                    <div class="mb-2">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($motel['category_name']); ?></span>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($motel['district_name']); ?></span>
                                    </div>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($motel['address']); ?>
                                    </p>
                                    
                                    <div class="row text-center text-muted small mb-3">
                                        <div class="col">
                                            <strong><?php echo number_format($motel['area']); ?></strong> m²
                                        </div>
                                        <div class="col">
                                            <strong><?php echo $motel['count_view']; ?></strong> lượt xem
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="text-danger fw-bold mb-0">
                                            <?php echo number_format($motel['price']); ?> đ
                                        </h5>
                                        <a href="/Case_Study/motel/detail.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Phân trang -->
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">Đầu tiên</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Trước</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?php echo $i; ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Sau</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>">Cuối cùng</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Không có phòng trọ nào để hiển thị
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
