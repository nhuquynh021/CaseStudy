<?php
// Trang chi tiết phòng trọ

include '../config/connect.php';

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy ID phòng trọ
$motel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($motel_id <= 0) {
    header("Location: /Case_Study/index.php");
    exit();
}

try {
    // Lấy thông tin phòng trọ
    $sql = "SELECT m.*, u.name as user_name, u.phone as user_phone, u.email as user_email,u.Avatar as Avatar,
                   d.name as district_name, c.name as category_name
            FROM motel m
            JOIN users u ON m.user_id = u.id
            JOIN districts d ON m.district_id = d.id
            JOIN category c ON m.category_id = c.id
            WHERE m.id = ? AND m.approve = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$motel_id]);
    
    if ($stmt->rowCount() == 0) {
        header("Location: /Case_Study/index.php");
        exit();
    }
    
    $motel = $stmt->fetch();
    
    // Tăng lượt xem
    $sql_view = "UPDATE motel SET count_view = count_view + 1 WHERE id = ?";
    $stmt_view = $conn->prepare($sql_view);
    $stmt_view->execute([$motel_id]);
    
    // Cập nhật lượt xem cho hiển thị
    $motel['count_view']++;
    
    // Lấy các phòng tương tự (cùng khu vực)
    $sql_similar = "SELECT m.*, u.name as user_name, d.name as district_name, c.name as category_name, u.avatar as Avatar
                    FROM motel m
                    JOIN users u ON m.user_id = u.id
                    JOIN districts d ON m.district_id = d.id
                    JOIN category c ON m.category_id = c.id
                    WHERE m.district_id = ? AND m.id != ? AND m.approve = 1
                    ORDER BY m.created_at DESC
                    LIMIT 3";
    $stmt_similar = $conn->prepare($sql_similar);
    $stmt_similar->execute([$motel['district_id'], $motel_id]);
    $similar_motels = $stmt_similar->fetchAll();
    
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <!-- Chi tiết phòng trọ -->
        <div class="col-md-8">
            <!-- Hình ảnh -->
            <div class="card mb-4 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div style="height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($motel['images']) && file_exists('../uploads/' . $motel['images'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($motel['images']); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <i class="bi bi-calendar"></i> Đăng: <?php echo date('d/m/Y', strtotime($motel['created_at'])); ?> |
                        <i class="bi bi-eye"></i> <?php echo number_format($motel['count_view']); ?> lượt xem
                    </small>
                </div>
            </div>
            
            <!-- Thông tin cơ bản -->
            <div class="card mb-4 shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-primary text-white" style="border-radius: 10px 10px 0 0;">
                    <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($motel['title']); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Giá:</h5>
                            <h3 class="text-danger fw-bold"><?php echo number_format($motel['price']); ?> đ/tháng</h3>
                        </div>
                        <div class="col-md-6">
                            <h5>Diện tích:</h5>
                            <h3 class="text-success fw-bold"><?php echo number_format($motel['area']); ?> m²</h3>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Loại phòng:</h5>
                            <p class="fs-5"><span class="badge bg-info"><?php echo htmlspecialchars($motel['category_name']); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Khu vực:</h5>
                            <p class="fs-5"><span class="badge bg-secondary"><?php echo htmlspecialchars($motel['district_name']); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Địa chỉ -->
            <div class="card mb-4 shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-success text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-geo-alt"></i> Địa chỉ</h5>
                </div>
                <div class="card-body">
                    <p class="fs-5"><strong><?php echo htmlspecialchars($motel['address']); ?></strong></p>
                </div>
            </div>
            
            <!-- Mô tả -->
            <div class="card mb-4 shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-info text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-text"></i> Mô tả</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($motel['description'])); ?></p>
                </div>
            </div>
            
            <!-- Tiện ích -->
            <?php if (!empty($motel['utilities'])): ?>
                <div class="card mb-4 shadow-sm" style="border-radius: 10px;">
                    <div class="card-header bg-warning text-dark" style="border-radius: 10px 10px 0 0;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-stars"></i> Tiện ích</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            $utilities = explode(',', $motel['utilities']);
                            foreach ($utilities as $utility):
                            ?>
                                <div class="col-md-6 mb-2">
                                    <i class="bi bi-check-circle text-success"></i> <?php echo trim($utility); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Google Maps -->
            <?php if (!empty($motel['latlng'])): ?>
                <div class="card mb-4 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                    <div class="card-header bg-dark text-white" style="border-radius: 10px 10px 0 0;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-map"></i> Bản đồ</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
                
                <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
                <script>
                    // Lưu ý: Thay YOUR_GOOGLE_MAPS_API_KEY bằng API key thực của bạn
                    const latlng = '<?php echo $motel['latlng']; ?>'.split(',');
                    const lat = parseFloat(latlng[0]);
                    const lng = parseFloat(latlng[1]);
                    
                    const map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 15,
                        center: { lat: lat, lng: lng }
                    });
                    
                    new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                        title: '<?php echo htmlspecialchars($motel['title']); ?>'
                    });
                </script>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar - Thông tin chủ trọ -->
        <div class="col-md-4">
            <!-- Thông tin chủ trọ -->
            <div class="card mb-4 shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-dark text-white" style="border-radius: 10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person"></i> Thông tin chủ trọ</h5>
                </div>

                 <div class="text-center mb-4">
                                <div style="width: 200px; height: 200px; margin: 0 auto; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                  <div class="card-body">
                     <div class="mb-3">
                        <?php if (!empty($motel['Avatar']) && file_exists('../' . $motel['Avatar'])): ?>
                            <img 
                                src="<?php echo('../' . $motel['Avatar']); ?>" 
                                alt="Avatar" 
                                class="rounded-circle" 
                                style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #007bff;">
                        <?php else: ?>
                            <i class="bi bi-person-circle" style="font-size: 5rem; color: #007bff;"></i>
                        <?php endif; ?>
                    </div>
            
                    </div> 
                                </div>
                            </div>
                <div class="card-body">             
                    <h5 class="text-center fw-bold"><?php echo htmlspecialchars($motel['user_name']); ?></h5>
                    
                    <div class="mb-3">
                        <strong><i class="bi bi-telephone"></i> Điện thoại:</strong><br>
                        <a href="tel:<?php echo htmlspecialchars($motel['phone']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($motel['phone']); ?>
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="bi bi-envelope"></i> Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($motel['user_email']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($motel['user_email']); ?>
                        </a>
                    </div>
                    
                    <button class="btn btn-primary w-100 mb-2" onclick="alert('Liên hệ: <?php echo htmlspecialchars($motel['phone']); ?>')">
                        <i class="bi bi-chat"></i> Liên hệ ngay
                    </button>
                    
                    <button class="btn btn-success w-100">
                        <i class="bi bi-heart"></i> Lưu phòng
                    </button>
                </div>
            </div>
            
            <!-- Phòng tương tự -->
            <?php if (count($similar_motels) > 0): ?>
                <div class="card shadow-sm" style="border-radius: 10px;">
                    <div class="card-header bg-dark text-white" style="border-radius: 10px 10px 0 0;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-grid-3x2"></i> Phòng tương tự</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($similar_motels as $similar): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="fw-bold mb-1">
                                    <a href="detail.php?id=<?php echo $similar['ID']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars(substr($similar['title'], 0, 30) . '...'); ?>
                                    </a>
                                </h6>
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($similar['district_name']); ?>
                                </small>
                                <small class="text-danger fw-bold">
                                    <?php echo number_format($similar['price']); ?> đ
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
