<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all providers for dropdowns
$stmt = $conn->query("SELECT id, name FROM Providers ORDER BY name");
$providersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get provider details
$provider1 = null;
$provider2 = null;
$services1 = [];
$services2 = [];
$contracts1 = [];
$contracts2 = [];

// Get provider 1 details
if (isset($_GET['provider1']) && !empty($_GET['provider1'])) {
    $stmt = $conn->prepare("SELECT * FROM Providers WHERE id = ?");
    $stmt->execute([(int)$_GET['provider1']]);
    $provider1 = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get services for provider 1
    if ($provider1) {
        $stmt = $conn->prepare("
            SELECT s.id, s.name, s.des, ps.providePrice, ps.currency, ps.unit 
            FROM Services s
            JOIN ProvideService ps ON s.id = ps.serviceId
            WHERE ps.providerId = ?
        ");
        $stmt->execute([(int)$_GET['provider1']]);
        $services1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get contracts for provider 1
        $stmt = $conn->prepare("
            SELECT c.*, s.name as serviceName 
            FROM Contracts c
            JOIN Services s ON c.serviceId = s.id
            WHERE c.providerId = ?
            ORDER BY c.signedDate DESC
        ");
        $stmt->execute([(int)$_GET['provider1']]);
        $contracts1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get provider 2 details
if (isset($_GET['provider2']) && !empty($_GET['provider2'])) {
    $stmt = $conn->prepare("SELECT * FROM Providers WHERE id = ?");
    $stmt->execute([(int)$_GET['provider2']]);
    $provider2 = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get services for provider 2
    if ($provider2) {
        $stmt = $conn->prepare("
            SELECT s.id, s.name, s.des, ps.providePrice, ps.currency, ps.unit 
            FROM Services s
            JOIN ProvideService ps ON s.id = ps.serviceId
            WHERE ps.providerId = ?
        ");
        $stmt->execute([(int)$_GET['provider2']]);
        $services2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get contracts for provider 2
        $stmt = $conn->prepare("
            SELECT c.*, s.name as serviceName 
            FROM Contracts c
            JOIN Services s ON c.serviceId = s.id
            WHERE c.providerId = ?
            ORDER BY c.signedDate DESC
        ");
        $stmt->execute([(int)$_GET['provider2']]);
        $contracts2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Helper function to display reputation stars
function displayReputationStars($reputation) {
    $maxStars = 5;
    $normalizedRep = min(5, round($reputation / 20)); // Convert from 0-100 to 0-5 scale
    
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $normalizedRep) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    $html .= ' <span class="ms-2">(' . $reputation . '/100)</span></div>';
    
    return $html;
}

include 'includes/header.php';
?>

<style>
.comparison-card {
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.comparison-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.provider-header {
    border-radius: 10px 10px 0 0;
    padding: 15px;
    position: relative;
}

.provider-header.left {
    background: linear-gradient(135deg, #4e73df, #224abe);
}

.provider-header.right {
    background: linear-gradient(135deg, #1cc88a, #169a6b);
}

.provider-header h3 {
    margin: 0;
    color: white;
    font-weight: bold;
}

.data-row {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    display: flex;
}

.data-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: 500;
    flex: 0 0 30%;
    color: #555;
}

.value {
    flex: 1;
    word-break: break-word;
}

.badge-active {
    background-color: #1cc88a;
}

.badge-inactive {
    background-color: #858796;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    padding: 12px 15px;
    background-color: #f8f9fc;
    border-bottom: 1px solid #eee;
    margin-top: 0;
}

.common-service {
    background-color: #fff9e6;
}

.better-price {
    position: relative;
}

.better-price::after {
    content: "✓ Better price";
    position: absolute;
    right: 15px;
    color: #1cc88a;
    font-weight: 600;
}

.star-rating {
    display: inline-flex;
    align-items: center;
}

.comparison-container {
    max-width: 1200px;
    margin: 0 auto;
}

.empty-provider-placeholder {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fc;
    border-radius: 10px;
    padding: 30px;
}

.empty-provider-placeholder i {
    font-size: 48px;
    color: #d1d3e2;
    margin-bottom: 15px;
}

.select-provider-btn {
    margin-top: 15px;
}

@media (max-width: 767.98px) {
    .mobile-column {
        margin-bottom: 30px;
    }
}
</style>

<!-- Main Content -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="m-0">So sánh chi tiết nhà cung cấp</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Nhà cung cấp 1</label>
                            <select class="form-select" name="provider1" required>
                                <option value="">Chọn nhà cung cấp</option>
                                <?php foreach ($providersList as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo (isset($_GET['provider1']) && $_GET['provider1'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nhà cung cấp 2</label>
                            <select class="form-select" name="provider2" required>
                                <option value="">Chọn nhà cung cấp</option>
                                <?php foreach ($providersList as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo (isset($_GET['provider2']) && $_GET['provider2'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">So sánh</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($provider1 || $provider2): ?>
    <div class="comparison-container">
        <div class="row">
            <!-- Provider 1 -->
            <div class="col-md-6 mobile-column">
                <?php if ($provider1): ?>
                <div class="comparison-card">
                    <div class="provider-header left">
                        <h3><?php echo htmlspecialchars($provider1['name']); ?></h3>
                    </div>
                    
                    <!-- General Information -->
                    <h4 class="section-title">Thông tin chung</h4>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-id-card me-2"></i>Mã số thuế</div>
                        <div class="value"><?php echo htmlspecialchars($provider1['taxCode']); ?></div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-percent me-2"></i>VAT</div>
                        <div class="value"><?php echo htmlspecialchars($provider1['vat']); ?></div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-tag me-2"></i>Trạng thái</div>
                        <div class="value">
                            <?php if ($provider1['status'] == 'Active'): ?>
                                <span class="badge badge-active">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Không hoạt động</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-star me-2"></i>Đánh giá</div>
                        <div class="value">
                            <?php echo displayReputationStars($provider1['reputation']); ?>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ</div>
                        <div class="value"><?php echo htmlspecialchars($provider1['address']); ?></div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h4 class="section-title">Thông tin liên hệ</h4>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-envelope me-2"></i>Email</div>
                        <div class="value">
                            <a href="mailto:<?php echo htmlspecialchars($provider1['email']); ?>">
                                <?php echo htmlspecialchars($provider1['email']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-phone me-2"></i>Điện thoại</div>
                        <div class="value">
                            <a href="tel:<?php echo htmlspecialchars($provider1['phone']); ?>">
                                <?php echo htmlspecialchars($provider1['phone']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-globe me-2"></i>Website</div>
                        <div class="value">
                            <?php if (!empty($provider1['website'])): ?>
                                <a href="<?php echo htmlspecialchars($provider1['website']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($provider1['website']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Services -->
                    <h4 class="section-title">Dịch vụ cung cấp</h4>
                    
                    <?php if (count($services1) > 0): ?>
                        <?php foreach ($services1 as $service): 
                            // Find if the same service exists in provider 2 for comparison
                            $matchingService = null;
                            $isBetterPrice = false;
                            
                            if ($provider2) {
                                foreach ($services2 as $s2) {
                                    if ($s2['id'] == $service['id']) {
                                        $matchingService = $s2;
                                        if ($service['providePrice'] < $s2['providePrice']) {
                                            $isBetterPrice = true;
                                        }
                                        break;
                                    }
                                }
                            }
                            
                            $rowClass = $matchingService ? 'common-service' : '';
                            $rowClass .= $isBetterPrice ? ' better-price' : '';
                        ?>
                        <div class="data-row <?php echo $rowClass; ?>">
                            <div class="label"><?php echo htmlspecialchars($service['name']); ?></div>
                            <div class="value">
                                <?php echo number_format($service['providePrice'], 2); ?>
                                <?php echo htmlspecialchars($service['currency']); ?>/
                                <?php echo htmlspecialchars($service['unit']); ?>
                                <div class="small text-muted"><?php echo htmlspecialchars($service['des']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-row">
                            <div class="text-muted fst-italic">Không có dịch vụ nào</div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contracts -->
                    <h4 class="section-title">Hợp đồng gần đây</h4>
                    
                    <?php if (count($contracts1) > 0): ?>
                        <?php $displayedContracts = 0; ?>
                        <?php foreach ($contracts1 as $contract): 
                            if ($displayedContracts >= 3) break; // Limit to 3 most recent contracts
                            $displayedContracts++;
                        ?>
                        <div class="data-row">
                            <div class="label"><?php echo htmlspecialchars($contract['name']); ?></div>
                            <div class="value">
                                <div><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($contract['serviceName']); ?></div>
                                <div>
                                    <strong>Giá trị:</strong> 
                                    <?php echo number_format($contract['price'], 2); ?>
                                    <?php echo htmlspecialchars($contract['currency']); ?>/
                                    <?php echo htmlspecialchars($contract['unit']); ?>
                                </div>
                                <div>
                                    <strong>Ngày ký:</strong> 
                                    <?php echo date('d/m/Y', strtotime($contract['signedDate'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-row">
                            <div class="text-muted fst-italic">Không có hợp đồng nào</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-3 text-center">
                        <a href="provider-details.php?id=<?php echo $provider1['id']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle me-2"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-provider-placeholder">
                    <i class="fas fa-building"></i>
                    <h4>Chưa chọn nhà cung cấp</h4>
                    <p>Vui lòng chọn nhà cung cấp để xem thông tin so sánh</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Provider 2 -->
            <div class="col-md-6">
                <?php if ($provider2): ?>
                <div class="comparison-card">
                    <div class="provider-header right">
                        <h3><?php echo htmlspecialchars($provider2['name']); ?></h3>
                    </div>
                    
                    <!-- General Information -->
                    <h4 class="section-title">Thông tin chung</h4>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-id-card me-2"></i>Mã số thuế</div>
                        <div class="value"><?php echo htmlspecialchars($provider2['taxCode']); ?></div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-percent me-2"></i>VAT</div>
                        <div class="value"><?php echo htmlspecialchars($provider2['vat']); ?></div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-tag me-2"></i>Trạng thái</div>
                        <div class="value">
                            <?php if ($provider2['status'] == 'Active'): ?>
                                <span class="badge badge-active">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Không hoạt động</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-star me-2"></i>Đánh giá</div>
                        <div class="value">
                            <?php echo displayReputationStars($provider2['reputation']); ?>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ</div>
                        <div class="value"><?php echo htmlspecialchars($provider2['address']); ?></div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h4 class="section-title">Thông tin liên hệ</h4>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-envelope me-2"></i>Email</div>
                        <div class="value">
                            <a href="mailto:<?php echo htmlspecialchars($provider2['email']); ?>">
                                <?php echo htmlspecialchars($provider2['email']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-phone me-2"></i>Điện thoại</div>
                        <div class="value">
                            <a href="tel:<?php echo htmlspecialchars($provider2['phone']); ?>">
                                <?php echo htmlspecialchars($provider2['phone']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="data-row">
                        <div class="label"><i class="fas fa-globe me-2"></i>Website</div>
                        <div class="value">
                            <?php if (!empty($provider2['website'])): ?>
                                <a href="<?php echo htmlspecialchars($provider2['website']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($provider2['website']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Services -->
                    <h4 class="section-title">Dịch vụ cung cấp</h4>
                    
                    <?php if (count($services2) > 0): ?>
                        <?php foreach ($services2 as $service): 
                            // Find if the same service exists in provider 1 for comparison
                            $matchingService = null;
                            $isBetterPrice = false;
                            
                            if ($provider1) {
                                foreach ($services1 as $s1) {
                                    if ($s1['id'] == $service['id']) {
                                        $matchingService = $s1;
                                        if ($service['providePrice'] < $s1['providePrice']) {
                                            $isBetterPrice = true;
                                        }
                                        break;
                                    }
                                }
                            }
                            
                            $rowClass = $matchingService ? 'common-service' : '';
                            $rowClass .= $isBetterPrice ? ' better-price' : '';
                        ?>
                        <div class="data-row <?php echo $rowClass; ?>">
                            <div class="label"><?php echo htmlspecialchars($service['name']); ?></div>
                            <div class="value">
                                <?php echo number_format($service['providePrice'], 2); ?>
                                <?php echo htmlspecialchars($service['currency']); ?>/
                                <?php echo htmlspecialchars($service['unit']); ?>
                                <div class="small text-muted"><?php echo htmlspecialchars($service['des']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-row">
                            <div class="text-muted fst-italic">Không có dịch vụ nào</div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contracts -->
                    <h4 class="section-title">Hợp đồng gần đây</h4>
                    
                    <?php if (count($contracts2) > 0): ?>
                        <?php $displayedContracts = 0; ?>
                        <?php foreach ($contracts2 as $contract): 
                            if ($displayedContracts >= 3) break; // Limit to 3 most recent contracts
                            $displayedContracts++;
                        ?>
                        <div class="data-row">
                            <div class="label"><?php echo htmlspecialchars($contract['name']); ?></div>
                            <div class="value">
                                <div><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($contract['serviceName']); ?></div>
                                <div>
                                    <strong>Giá trị:</strong> 
                                    <?php echo number_format($contract['price'], 2); ?>
                                    <?php echo htmlspecialchars($contract['currency']); ?>/
                                    <?php echo htmlspecialchars($contract['unit']); ?>
                                </div>
                                <div>
                                    <strong>Ngày ký:</strong> 
                                    <?php echo date('d/m/Y', strtotime($contract['signedDate'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-row">
                            <div class="text-muted fst-italic">Không có hợp đồng nào</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-3 text-center">
                        <a href="provider-details.php?id=<?php echo $provider2['id']; ?>" class="btn btn-outline-success">
                            <i class="fas fa-info-circle me-2"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-provider-placeholder">
                    <i class="fas fa-building"></i>
                    <h4>Chưa chọn nhà cung cấp</h4>
                    <p>Vui lòng chọn nhà cung cấp để xem thông tin so sánh</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 