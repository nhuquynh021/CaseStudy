<?php
// Trang tìm kiếm và lọc phòng trọ

include '../config/connect.php';

// Khởi động session
// if (session_status() === PHP_SESSION_NONE) {
    
// }
session_start();

// Lấy dữ liệu lọc
$keyword = trim($_GET['keyword'] ?? '');
$price_min = isset($_GET['price_min']) && $_GET['price_min'] != '' ? (int)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] != '' ? (int)$_GET['price_max'] : 999999999;
$area_min = isset($_GET['area_min']) && $_GET['area_min'] != '' ? (int)$_GET['area_min'] : 0;
$area_max = isset($_GET['area_max']) && $_GET['area_max'] != '' ? (int)$_GET['area_max'] : 999999999;
$district_id = isset($_GET['district_id']) && $_GET['district_id'] != '' ? (int)$_GET['district_id'] : 0;
$category_id = isset($_GET['category_id']) && $_GET['category_id'] != '' ? (int)$_GET['category_id'] : 0;

// Lấy trang hiện tại (phân trang)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

try {
    // Xây dựng điều kiện tìm kiếm
    $where = "WHERE m.approve = 1";
    $params = [];
    
    if (!empty($keyword)) {
        $where .= " AND (m.title LIKE ? OR m.address LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }
    
    $where .= " AND m.price BETWEEN ? AND ?";
    $params[] = $price_min;
    $params[] = $price_max;
    
    $where .= " AND m.area BETWEEN ? AND ?";
    $params[] = $area_min;
    $params[] = $area_max;
    
    if ($district_id > 0) {
        $where .= " AND m.district_id = ?";
        $params[] = $district_id;
    }
    
    if ($category_id > 0) {
        $where .= " AND m.category_id = ?";
        $params[] = $category_id;
    }
    
    // Tổng số phòng
    $sql_count = "SELECT COUNT(*) as total FROM motel m $where";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_motels = $stmt_count->fetch()['total'];
    $total_pages = ceil($total_motels / $items_per_page);
    
    // Lấy phòng trọ của trang hiện tại
    $sql = "SELECT m.*, u.name as user_name, u.phone as user_phone, d.name as district_name, c.name as category_name
            FROM motel m
            JOIN users u ON m.user_id = u.id
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            $where
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

// Bind các điều kiện tìm kiếm trước
$paramIndex = 1;
foreach ($params as $param) {
    $stmt->bindValue($paramIndex++, $param);
}

// Bind LIMIT và OFFSET kiểu số nguyên
$stmt->bindValue($paramIndex++, $items_per_page, PDO::PARAM_INT);
$stmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);

$stmt->execute();
$motels = $stmt->fetchAll();
    
    // Lấy danh sách quận huyện
    $sql_districts = "SELECT * FROM districts";
    $result_districts = $conn->query($sql_districts);
    $districts = $result_districts->fetchAll();
    
    // Lấy danh sách loại phòng
    $sql_categories = "SELECT * FROM category";
    $result_categories = $conn->query($sql_categories);
    $categories = $result_categories->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar - Bộ lọc -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-dark text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-funnel"></i> Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <!-- Tìm kiếm theo từ khóa -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Từ khóa</label>
                            <input 
                                type="text" 
                                name="keyword" 
                                class="form-control" 
                                placeholder="Tìm kiếm..."
                                value="<?php echo htmlspecialchars($keyword); ?>"
                            >
                        </div>
                        
                        <!-- Lọc theo giá -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá (đ/tháng)</label>
                            <div class="row">
                                <div class="col-6">
                                    <input 
                                        type="number" 
                                        name="price_min" 
                                        class="form-control form-control-sm" 
                                        placeholder="Từ"
                                        value="<?php echo $price_min > 0 ? $price_min : ''; ?>"
                                    >
                                </div>
                                <div class="col-6">
                                    <input 
                                        type="number" 
                                        name="price_max" 
                                        class="form-control form-control-sm" 
                                        placeholder="Đến"
                                        value="<?php echo $price_max < 999999999 ? $price_max : ''; ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lọc theo diện tích -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Diện tích (m²)</label>
                            <div class="row">
                                <div class="col-6">
                                    <input 
                                        type="number" 
                                        name="area_min" 
                                        class="form-control form-control-sm" 
                                        placeholder="Từ"
                                        value="<?php echo $area_min > 0 ? $area_min : ''; ?>"
                                    >
                                </div>
                                <div class="col-6">
                                    <input 
                                        type="number" 
                                        name="area_max" 
                                        class="form-control form-control-sm" 
                                        placeholder="Đến"
                                        value="<?php echo $area_max < 999999999 ? $area_max : ''; ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lọc theo khu vực -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khu vực</label>
                            <select name="district_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($districts as $dist): ?>
                                    <option value="<?php echo $dist['ID']; ?>" <?php echo $district_id == $dist['ID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dist['Name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Lọc theo loại phòng -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Loại phòng</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['ID']; ?>" <?php echo $category_id == $cat['ID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['Name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-search"></i> Tìm kiếm
                        </button>
                        <a href="search.php" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Danh sách phòng trọ -->
        <div class="col-md-9">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="fw-bold">
                        <i class="bi bi-list"></i> Kết quả tìm kiếm 
                        <span class="text-primary">(<?php echo number_format($total_motels); ?> phòng)</span>
                    </h4>
                </div>
            </div>
            
            <?php if (count($motels) > 0): ?>
                <div class="row">
                    <?php foreach ($motels as $motel): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                    <?php if (!empty($motel['images']) && file_exists('../uploads/' . $motel['images'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
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
                                        <div class="col-6">
                                            <strong><?php echo number_format($motel['area']); ?></strong> m²
                                        </div>
                                        <div class="col-6">
                                            <strong><?php echo $motel['count_view']; ?></strong> lượt xem
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="text-danger fw-bold mb-0">
                                            <?php echo number_format($motel['price']); ?> đ
                                        </h5>
                                        <a href="detail.php?id=<?php echo $motel['ID']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">Đầu tiên</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Trước</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Sau</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">Cuối cùng</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Không tìm thấy phòng trọ nào phù hợp với tiêu chí của bạn
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
