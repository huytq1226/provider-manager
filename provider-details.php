<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get provider ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no valid ID provided
if ($id <= 0) {
    header('Location: providers.php');
    exit;
}

// Get provider details
$sql = "SELECT * FROM Providers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$provider = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if provider not found
if (!$provider) {
    header('Location: providers.php');
    exit;
}

// Get services provided by this provider
$serviceSql = "SELECT s.*, ps.providePrice, ps.currency, ps.unit 
               FROM Services s
               JOIN ProvideService ps ON s.id = ps.serviceId
               WHERE ps.providerId = ?";
$serviceStmt = $conn->prepare($serviceSql);
$serviceStmt->execute([$id]);
$services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);

// Get contracts with this provider
$contractSql = "SELECT c.*, s.name as serviceName
                FROM Contracts c
                JOIN Services s ON c.serviceId = s.id
                WHERE c.providerId = ?
                ORDER BY c.signedDate DESC";
$contractStmt = $conn->prepare($contractSql);
$contractStmt->execute([$id]);
$contracts = $contractStmt->fetchAll(PDO::FETCH_ASSOC);

// Get rating criteria
try {
    $criteriaStmt = $conn->query("SELECT * FROM RatingCriteria WHERE status = 'Active' ORDER BY weight DESC");
    $ratingCriteria = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get existing ratings for this provider
    $ratingsStmt = $conn->prepare("
        SELECT pr.*, 
               GROUP_CONCAT(CONCAT(rc.name, ':', rs.score) SEPARATOR '|') as criteria_scores
        FROM ProviderRatings pr
        LEFT JOIN RatingScores rs ON pr.id = rs.ratingId
        LEFT JOIN RatingCriteria rc ON rs.criteriaId = rc.id
        WHERE pr.providerId = ?
        GROUP BY pr.id
        ORDER BY pr.createDate DESC
        LIMIT 10
    ");
    $ratingsStmt->execute([$id]);
    $ratings = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate average scores for each criterion
    $avgScoresStmt = $conn->prepare("
        SELECT rc.id, rc.name, AVG(rs.score) as avg_score
        FROM RatingCriteria rc
        JOIN RatingScores rs ON rc.id = rs.criteriaId
        JOIN ProviderRatings pr ON rs.ratingId = pr.id
        WHERE pr.providerId = ?
        GROUP BY rc.id
        ORDER BY rc.weight DESC
    ");
    $avgScoresStmt->execute([$id]);
    $avgScores = $avgScoresStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If tables don't exist yet, just continue with empty arrays
    $ratingCriteria = [];
    $ratings = [];
    $avgScores = [];
}

include 'includes/header.php';
?>

<!-- Add CSS for star rating -->
<style>
    .rating-container {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    
    .rating-container input {
        display: none;
    }
    
    .rating-container label {
        cursor: pointer;
        width: 30px;
        height: 30px;
        margin-right: 5px;
        position: relative;
        font-size: 30px;
        color: #ddd;
    }
    
    .rating-container label:before {
        content: "\2605";
        position: absolute;
        opacity: 0;
    }
    
    .rating-container label:hover:before,
    .rating-container label:hover ~ label:before,
    .rating-container input:checked ~ label:before {
        opacity: 1;
        color: #ffc107;
    }
    
    .rating-container input:checked ~ label:before {
        opacity: 1;
    }
    
    .rating-summary {
        margin-top: 20px;
        margin-bottom: 30px;
    }
    
    .criteria-progress {
        height: 10px;
        border-radius: 5px;
    }
    
    .rating-card {
        border-left: 4px solid #4e73df;
        margin-bottom: 15px;
    }
    
    .rating-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
        margin-bottom: 10px;
    }
    
    .rating-date {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .rating-score {
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        color: white;
    }
    
    .score-excellent {
        background-color: #1cc88a;
    }
    
    .score-good {
        background-color: #4e73df;
    }
    
    .score-average {
        background-color: #f6c23e;
    }
    
    .score-poor {
        background-color: #e74a3b;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="providers.php">Nhà cung cấp</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($provider['name']); ?></li>
                </ol>
            </nav>
            
            <div class="card shadow-hover fade-in mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($provider['name']); ?></h4>
                        <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> <?php echo $provider['reputation']; ?>/100</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Thông tin chung</h5>
                            <table class="table table-hover">
                                <tr>
                                    <th width="35%"><i class="fas fa-id-card me-2"></i>Mã số thuế:</th>
                                    <td><?php echo htmlspecialchars($provider['taxCode']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-percent me-2"></i>VAT:</th>
                                    <td><?php echo htmlspecialchars($provider['vat']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-tag me-2"></i>Trạng thái:</th>
                                    <td>
                                        <?php if ($provider['status'] == 'Active'): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Không hoạt động</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</th>
                                    <td><?php echo htmlspecialchars($provider['address']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Thông tin liên hệ</h5>
                            <table class="table table-hover">
                                <tr>
                                    <th width="35%"><i class="fas fa-envelope me-2"></i>Email:</th>
                                    <td><a href="mailto:<?php echo htmlspecialchars($provider['email']); ?>"><?php echo htmlspecialchars($provider['email']); ?></a></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone me-2"></i>Điện thoại:</th>
                                    <td><a href="tel:<?php echo htmlspecialchars($provider['phone']); ?>"><?php echo htmlspecialchars($provider['phone']); ?></a></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-globe me-2"></i>Website:</th>
                                    <td>
                                        <?php if (!empty($provider['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($provider['website']); ?>" target="_blank"><?php echo htmlspecialchars($provider['website']); ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa cập nhật</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar-alt me-2"></i>Ngày tạo:</th>
                                    <td><?php echo date('d/m/Y', strtotime($provider['createDate'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Services Provided -->
                    <div class="mt-4">
                        <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-cogs me-2"></i>Dịch vụ cung cấp</h5>
                        <?php if (count($services) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tên dịch vụ</th>
                                            <th>Mô tả</th>
                                            <th>Giá cung cấp</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $service): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                                <td><?php echo htmlspecialchars($service['des']); ?></td>
                                                <td>
                                                    <?php echo number_format($service['providePrice'], 2); ?> 
                                                    <?php echo htmlspecialchars($service['currency']); ?>/
                                                    <?php echo htmlspecialchars($service['unit']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($service['status'] == 'Active'): ?>
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Không hoạt động</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Nhà cung cấp này chưa có dịch vụ nào.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Contracts -->
                    <div class="mt-4">
                        <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-file-contract me-2"></i>Hợp đồng</h5>
                        <?php if (count($contracts) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tên hợp đồng</th>
                                            <th>Dịch vụ</th>
                                            <th>Giá trị</th>
                                            <th>Ngày ký</th>
                                            <th>Ngày hết hạn</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contract['name']); ?></td>
                                                <td><?php echo htmlspecialchars($contract['serviceName']); ?></td>
                                                <td>
                                                    <?php echo number_format($contract['price'], 2); ?> 
                                                    <?php echo htmlspecialchars($contract['currency']); ?>/
                                                    <?php echo htmlspecialchars($contract['unit']); ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($contract['signedDate'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($contract['expiredDate'])); ?></td>
                                                <td>
                                                    <?php if ($contract['status'] == 'Active'): ?>
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Không hoạt động</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Nhà cung cấp này chưa có hợp đồng nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="providers.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại danh sách</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <button type="button" class="btn btn-primary" onclick="editProvider(<?php echo htmlspecialchars(json_encode($provider)); ?>)" data-bs-toggle="modal" data-bs-target="#editProviderModal">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteProviderConfirm(<?php echo $provider['id']; ?>, '<?php echo htmlspecialchars($provider['name']); ?>')">
                            <i class="fas fa-trash-alt me-2"></i>Xóa
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Section -->
    <div class="row mt-4">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-hover fade-in mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-star me-2"></i>Đánh giá nhà cung cấp</h4>
                </div>
                <div class="card-body">
                    <!-- Rating Summary -->
                    <div class="row rating-summary">
                        <div class="col-md-4 text-center">
                            <div class="h1 mb-0"><?php echo number_format($provider['reputation'] / 20, 1); ?></div>
                            <div class="text-warning mb-2">
                                <?php
                                $stars = round($provider['reputation'] / 20);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $stars) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="text-muted"><?php echo count($ratings); ?> đánh giá</div>
                        </div>
                        <div class="col-md-8">
                            <?php if (!empty($avgScores)): ?>
                                <?php foreach ($avgScores as $score): ?>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><?php echo htmlspecialchars($score['name']); ?></span>
                                            <span><?php echo number_format($score['avg_score'], 1); ?></span>
                                        </div>
                                        <div class="progress criteria-progress">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: <?php echo ($score['avg_score'] / 5) * 100; ?>%" 
                                                 aria-valuenow="<?php echo $score['avg_score']; ?>" 
                                                 aria-valuemin="0" aria-valuemax="5"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">Chưa có đánh giá chi tiết cho nhà cung cấp này.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Rating Form -->
                    <form method="POST" action="process_rating.php" class="border-top pt-4">
                        <input type="hidden" name="provider_id" value="<?php echo $id; ?>">
                        
                        <div class="mb-4">
                            <h5>Đánh giá mới</h5>
                            
                            <?php if (!empty($contracts)): ?>
                            <div class="mb-3">
                                <label class="form-label">Chọn hợp đồng (tùy chọn)</label>
                                <select name="contract_id" class="form-select">
                                    <option value="">-- Không liên quan đến hợp đồng cụ thể --</option>
                                    <?php foreach ($contracts as $contract): ?>
                                    <option value="<?php echo $contract['id']; ?>">
                                        <?php echo htmlspecialchars($contract['name']); ?> (<?php echo date('d/m/Y', strtotime($contract['signedDate'])); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($ratingCriteria)): ?>
                                <?php foreach ($ratingCriteria as $criteria): ?>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo htmlspecialchars($criteria['name']); ?></label>
                                    <div class="rating-container">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="score_<?php echo $criteria['id']; ?>_<?php echo $i; ?>" 
                                               name="score_<?php echo $criteria['id']; ?>" value="<?php echo $i; ?>" 
                                               <?php echo $i == 5 ? 'required' : ''; ?>>
                                        <label for="score_<?php echo $criteria['id']; ?>_<?php echo $i; ?>" 
                                               title="<?php echo $i; ?> sao"></label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="form-text"><?php echo htmlspecialchars($criteria['description']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Hệ thống đánh giá chưa được thiết lập. Vui lòng chạy script SQL để tạo các bảng đánh giá.
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Nhận xét</label>
                                <textarea class="form-control" name="comment" rows="3" placeholder="Nhận xét của bạn về nhà cung cấp này..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" <?php echo empty($ratingCriteria) ? 'disabled' : ''; ?>>
                                    Gửi đánh giá
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Recent Ratings -->
                    <?php if (!empty($ratings)): ?>
                    <div class="mt-4">
                        <h5 class="mb-3">Đánh giá gần đây</h5>
                        
                        <?php foreach ($ratings as $rating): 
                            // Parse the criteria scores
                            $criteriaScores = [];
                            if (!empty($rating['criteria_scores'])) {
                                $scores = explode('|', $rating['criteria_scores']);
                                foreach ($scores as $score) {
                                    if (strpos($score, ':') !== false) {
                                        list($name, $value) = explode(':', $score);
                                        $criteriaScores[$name] = $value;
                                    }
                                }
                            }
                            
                            // Determine rating score class
                            $scoreClass = 'score-average';
                            if ($rating['overall'] >= 4.5) {
                                $scoreClass = 'score-excellent';
                            } elseif ($rating['overall'] >= 3.5) {
                                $scoreClass = 'score-good';
                            } elseif ($rating['overall'] < 2.5) {
                                $scoreClass = 'score-poor';
                            }
                        ?>
                        <div class="card rating-card">
                            <div class="card-body">
                                <div class="rating-header">
                                    <div class="rating-date">
                                        <?php echo date('d/m/Y H:i', strtotime($rating['createDate'])); ?>
                                    </div>
                                    <span class="rating-score <?php echo $scoreClass; ?>">
                                        <?php echo number_format($rating['overall'], 1); ?>/5
                                    </span>
                                </div>
                                
                                <?php if (!empty($criteriaScores)): ?>
                                <div class="d-flex flex-wrap mb-2">
                                    <?php foreach ($criteriaScores as $name => $value): ?>
                                    <div class="me-3 mb-1">
                                        <small class="text-muted"><?php echo htmlspecialchars($name); ?>:</small>
                                        <span class="ms-1">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $value) {
                                                    echo '<i class="fas fa-star text-warning" style="font-size: 0.8rem;"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-warning" style="font-size: 0.8rem;"></i>';
                                                }
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($rating['comment'])): ?>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($rating['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="editProviderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="process_provider.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã số thuế</label>
                        <input type="text" class="form-control" name="taxCode" id="edit_taxCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VAT</label>
                        <input type="text" class="form-control" name="vat" id="edit_vat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Active">Hoạt động</option>
                            <option value="Inactive">Không hoạt động</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" name="address" id="edit_address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website" id="edit_website">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Điểm uy tín (1-100)</label>
                        <input type="number" class="form-control" name="reputation" id="edit_reputation" min="1" max="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Provider Confirmation Modal -->
<div class="modal fade" id="deleteProviderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa nhà cung cấp <span id="delete_provider_name" class="fw-bold"></span>?</p>
                <p class="text-danger">Lưu ý: Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan đến nhà cung cấp này.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="process_provider.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_provider_id">
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editProvider(provider) {
        document.getElementById('edit_id').value = provider.id;
        document.getElementById('edit_name').value = provider.name;
        document.getElementById('edit_taxCode').value = provider.taxCode;
        document.getElementById('edit_vat').value = provider.vat;
        document.getElementById('edit_status').value = provider.status;
        document.getElementById('edit_address').value = provider.address;
        document.getElementById('edit_email').value = provider.email;
        document.getElementById('edit_phone').value = provider.phone;
        document.getElementById('edit_website').value = provider.website;
        document.getElementById('edit_reputation').value = provider.reputation;
    }
    
    function deleteProviderConfirm(id, name) {
        document.getElementById('delete_provider_id').value = id;
        document.getElementById('delete_provider_name').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteProviderModal')).show();
    }
</script>

<?php include 'includes/footer.php'; ?> 