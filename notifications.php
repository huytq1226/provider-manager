<?php
require_once 'includes/init.php';

// Biến để bật/tắt debug - chỉ hiển thị cho admin
$enableDebug = true; // Đặt thành false để tắt debug trong môi trường sản xuất

// Calculate date for expiring contracts (next 30 days)
$today = new DateTime();
$thirtyDaysLater = clone $today;
$thirtyDaysLater->modify('+30 days');
$todayFormatted = $today->format('Y-m-d');
$thirtyDaysLaterFormatted = $thirtyDaysLater->format('Y-m-d');

// Debug: In ra khoảng thời gian được sử dụng để tìm hợp đồng sắp hết hạn
$debug = [];
$debug['date_range'] = "Tìm hợp đồng từ $todayFormatted đến $thirtyDaysLaterFormatted";

// Kiểm tra định dạng ngày tháng trong CSDL
$formatCheck = $conn->query("SELECT expiredDate FROM Contracts LIMIT 5");
$dateExamples = $formatCheck->fetchAll(PDO::FETCH_COLUMN);
$debug['date_examples'] = $dateExamples;

// Get expiring contracts - Sửa lại truy vấn
$contractsQuery = "
    SELECT c.*, p.name as providerName, s.name as serviceName 
    FROM Contracts c 
    LEFT JOIN Providers p ON c.providerId = p.id 
    LEFT JOIN Services s ON c.serviceId = s.id
    WHERE c.status = 'Active' 
    AND (
        c.expiredDate BETWEEN ? AND ? 
        OR DATE(c.expiredDate) BETWEEN ? AND ?
        OR STR_TO_DATE(c.expiredDate, '%d/%m/%Y') BETWEEN ? AND ?
        OR STR_TO_DATE(c.expiredDate, '%Y/%m/%d') BETWEEN ? AND ?
    )
    ORDER BY c.expiredDate ASC
";
$debug['query'] = $contractsQuery;
$debug['params'] = [
    $todayFormatted, $thirtyDaysLaterFormatted, 
    $todayFormatted, $thirtyDaysLaterFormatted,
    $todayFormatted, $thirtyDaysLaterFormatted,
    $todayFormatted, $thirtyDaysLaterFormatted
];

$contractsStmt = $conn->prepare($contractsQuery);
$contractsStmt->execute([
    $todayFormatted, $thirtyDaysLaterFormatted, 
    $todayFormatted, $thirtyDaysLaterFormatted,
    $todayFormatted, $thirtyDaysLaterFormatted,
    $todayFormatted, $thirtyDaysLaterFormatted
]);
$expiringContracts = $contractsStmt->fetchAll(PDO::FETCH_ASSOC);
$debug['sql_contracts_count'] = count($expiringContracts);

// Giải pháp dự phòng: Kiểm tra thủ công từng hợp đồng có trạng thái Active
if (count($expiringContracts) == 0) {
    $allActiveContractsStmt = $conn->query("
        SELECT c.*, p.name as providerName, s.name as serviceName 
        FROM Contracts c 
        LEFT JOIN Providers p ON c.providerId = p.id 
        LEFT JOIN Services s ON c.serviceId = s.id
        WHERE c.status = 'Active' 
        ORDER BY c.expiredDate ASC
    ");
    $allActiveContracts = $allActiveContractsStmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['active_contracts_count'] = count($allActiveContracts);
    
    // Kiểm tra thủ công từng hợp đồng
    foreach ($allActiveContracts as $contract) {
        // Thử chuyển đổi ngày theo nhiều định dạng
        $expireDate = null;
        $formats = ['Y-m-d', 'd/m/Y', 'Y/m/d', 'm/d/Y'];
        
        foreach ($formats as $format) {
            $dateObj = DateTime::createFromFormat($format, $contract['expiredDate']);
            if ($dateObj !== false) {
                $expireDate = $dateObj;
                break;
            }
        }
        
        // Nếu có thể chuyển đổi thành công ngày hết hạn
        if ($expireDate !== null) {
            $diff = $today->diff($expireDate);
            $daysRemaining = $diff->days;
            
            // Nếu còn dưới 30 ngày và sau ngày hiện tại
            if ($daysRemaining <= 30 && $expireDate >= $today) {
                $expiringContracts[] = $contract;
                
                if ($enableDebug) {
                    // Lưu thông tin debug
                    if (!isset($debug['manual_checks'])) {
                        $debug['manual_checks'] = [];
                    }
                    $debug['manual_checks'][] = [
                        'id' => $contract['id'],
                        'name' => $contract['name'],
                        'expiredDate' => $contract['expiredDate'],
                        'detected_format' => $format,
                        'days_remaining' => $daysRemaining
                    ];
                }
            }
        }
    }
    
    // Sắp xếp lại theo ngày hết hạn
    usort($expiringContracts, function($a, $b) {
        return $a['expiredDate'] <=> $b['expiredDate'];
    });
}

// Kiểm tra tất cả các hợp đồng
$allContractsStmt = $conn->query("SELECT id, name, status, expiredDate FROM Contracts ORDER BY expiredDate");
$allContracts = $allContractsStmt->fetchAll(PDO::FETCH_ASSOC);
$debug['all_contracts'] = $allContracts;
$debug['expiring_contracts'] = $expiringContracts;

// Get pending bills
$billsStmt = $conn->prepare("
    SELECT b.*, c.name as contractName 
    FROM Bills b 
    LEFT JOIN Contracts c ON b.refContractId = c.id 
    WHERE b.status = 'Pending'
    ORDER BY b.createdDate DESC
");
$billsStmt->execute();
$pendingBills = $billsStmt->fetchAll(PDO::FETCH_ASSOC);

// Count total notifications
$totalNotifications = count($expiringContracts) + count($pendingBills);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <?php if ($enableDebug && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <!-- Debug Information (chỉ hiển thị cho admin) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-hover">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-bug me-2"></i> Debug Information
                    </h3>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                        <i class="fas fa-code"></i> Toggle Debug
                    </button>
                </div>
                <div class="card-body collapse" id="debugInfo">
                    <div class="alert alert-info">
                        <h4>Thông tin debug:</h4>
                        <ul>
                            <li><?php echo $debug['date_range']; ?></li>
                            <li>Số hợp đồng từ SQL: <?php echo $debug['sql_contracts_count']; ?></li>
                            <li>Tổng số hợp đồng sắp hết hạn: <?php echo count($expiringContracts); ?></li>
                        </ul>
                        
                        <?php if (isset($debug['active_contracts_count'])): ?>
                        <h5>Thông tin kiểm tra thủ công:</h5>
                        <ul>
                            <li>Số hợp đồng Active: <?php echo $debug['active_contracts_count']; ?></li>
                        </ul>
                            
                        <?php if (isset($debug['manual_checks']) && !empty($debug['manual_checks'])): ?>
                            <h5>Hợp đồng được thêm thủ công:</h5>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên</th>
                                        <th>Ngày hết hạn</th>
                                        <th>Định dạng phát hiện</th>
                                        <th>Ngày còn lại</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($debug['manual_checks'] as $check): ?>
                                    <tr>
                                        <td><?php echo $check['id']; ?></td>
                                        <td><?php echo $check['name']; ?></td>
                                        <td><?php echo $check['expiredDate']; ?></td>
                                        <td><?php echo $check['detected_format']; ?></td>
                                        <td><?php echo $check['days_remaining']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <h5>Ví dụ định dạng ngày trong DB:</h5>
                        <pre><?php print_r($debug['date_examples']); ?></pre>
                        
                        <h5>Hợp đồng sắp hết hạn:</h5>
                        <pre><?php print_r($debug['expiring_contracts']); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-hover">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i> Trung tâm thông báo
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($totalNotifications > 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Bạn có <strong><?php echo $totalNotifications; ?></strong> thông báo cần chú ý
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Không có thông báo nào cần chú ý
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Expiring Contracts -->
        <div class="col-lg-6">
            <div class="card shadow-hover h-100 fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-contract text-warning me-2"></i> Hợp đồng sắp hết hạn
                    </h3>
                    <span class="badge bg-warning rounded-pill">
                        <?php echo count($expiringContracts); ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (count($expiringContracts) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hợp đồng</th>
                                        <th>Nhà cung cấp</th>
                                        <th>Dịch vụ</th>
                                        <th>Ngày hết hạn</th>
                                        <th>Ngày còn lại</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expiringContracts as $contract): 
                                        $expireDate = new DateTime($contract['expiredDate']);
                                        $daysRemaining = $today->diff($expireDate)->days;
                                        $badgeClass = ($daysRemaining <= 7) ? 'danger' : (($daysRemaining <= 15) ? 'warning' : 'info');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contract['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['providerName']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['serviceName']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['expiredDate']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                                <?php echo $daysRemaining; ?> ngày
                                            </span>
                                        </td>
                                        <td>
                                            <a href="contracts.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Không có hợp đồng nào sắp hết hạn
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="contracts.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> Xem tất cả hợp đồng
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Bills -->
        <div class="col-lg-6">
            <div class="card shadow-hover h-100 fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar text-danger me-2"></i> Hóa đơn chưa thanh toán
                    </h3>
                    <span class="badge bg-danger rounded-pill">
                        <?php echo count($pendingBills); ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (count($pendingBills) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hóa đơn</th>
                                        <th>Mô tả</th>
                                        <th>Hợp đồng</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingBills as $bill): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bill['name']); ?></td>
                                        <td><?php echo htmlspecialchars($bill['des']); ?></td>
                                        <td><?php echo htmlspecialchars($bill['contractName'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($bill['createdDate']); ?></td>
                                        <td>
                                            <a href="bills.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Không có hóa đơn nào đang chờ thanh toán
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="bills.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> Xem tất cả hóa đơn
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-hover fade-in">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i> Tổng quan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Hợp đồng sắp hết hạn</h6>
                                            <h2 class="mt-2 mb-0"><?php echo count($expiringContracts); ?></h2>
                                        </div>
                                        <i class="fas fa-file-contract fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Hóa đơn chưa thanh toán</h6>
                                            <h2 class="mt-2 mb-0"><?php echo count($pendingBills); ?></h2>
                                        </div>
                                        <i class="fas fa-file-invoice-dollar fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Tổng số thông báo</h6>
                                            <h2 class="mt-2 mb-0"><?php echo $totalNotifications; ?></h2>
                                        </div>
                                        <i class="fas fa-bell fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Ngày hiện tại</h6>
                                            <h2 class="mt-2 mb-0"><?php echo date('d/m/Y'); ?></h2>
                                        </div>
                                        <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 