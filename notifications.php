<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/notifications.php';
require_once 'includes/auth.php';

// Check if database connection is valid
if (!isset($conn) || !$conn) {
    // Show error message
    $_SESSION['error'] = "Không thể kết nối đến cơ sở dữ liệu.";
    include 'includes/header.php';
    echo '<div class="container-fluid">';
    echo '<div class="alert alert-danger">Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra cấu hình kết nối.</div>';
    echo '</div>';
    include 'includes/footer.php';
    exit;
}

// Check if there are no notifications and add sample ones if needed
try {
    $checkStmt = $conn->query("SELECT COUNT(*) FROM Notifications");
    $notificationCount = (int) $checkStmt->fetchColumn();
    
    if ($notificationCount === 0) {
        // Insert sample notifications if none exist
        $sampleNotifications = [
            // Urgent bill notifications
            [
                'bill_due', 'urgent', 'Hóa đơn sắp đến hạn: Dịch vụ Cloud Server T8/2023',
                'Hóa đơn "Dịch vụ Cloud Server T8/2023" sẽ đến hạn vào ngày 25/08/2023.', 
                1, 'Bills', false, null, date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('+7 days'))
            ],
            [
                'bill_due', 'urgent', 'Hóa đơn sắp đến hạn: Phí bản quyền phần mềm',
                'Hóa đơn "Phí bản quyền phần mềm" sẽ đến hạn vào ngày 27/08/2023.', 
                2, 'Bills', false, null, date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('+5 days'))
            ],
            
            // Warning contract notifications
            [
                'contract_expiring', 'warning', 'Hợp đồng sắp hết hạn: Dịch vụ bảo trì hệ thống',
                'Hợp đồng "Dịch vụ bảo trì hệ thống" với nhà cung cấp "Tech Solutions" sẽ hết hạn vào ngày 15/09/2023.', 
                3, 'Contracts', false, null, date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('+14 days'))
            ],
            [
                'contract_expiring', 'warning', 'Hợp đồng sắp hết hạn: Dịch vụ Email doanh nghiệp',
                'Hợp đồng "Dịch vụ Email doanh nghiệp" với nhà cung cấp "Google Workspace" sẽ hết hạn vào ngày 10/09/2023.', 
                4, 'Contracts', false, null, date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('+12 days'))
            ],
            
            // Info notifications
            [
                'system', 'info', 'Cập nhật hệ thống',
                'Hệ thống sẽ được cập nhật phiên bản mới vào ngày 30/08/2023 từ 22:00 - 23:00.', 
                null, null, false, null, date('Y-m-d H:i:s', strtotime('-5 days')), date('Y-m-d H:i:s', strtotime('+10 days'))
            ],
            [
                'provider_update', 'info', 'Cập nhật thông tin nhà cung cấp: VNPT',
                'Nhà cung cấp VNPT đã cập nhật thông tin liên hệ và bảng giá dịch vụ mới.', 
                6, 'Providers', false, null, date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('+20 days'))
            ],
            
            // Read notifications
            [
                'bill_due', 'info', 'Hóa đơn sắp đến hạn: Dịch vụ Backup T7/2023',
                'Hóa đơn "Dịch vụ Backup T7/2023" sẽ đến hạn vào ngày 20/08/2023.', 
                7, 'Bills', true, date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('+5 days'))
            ],
            [
                'contract_expiring', 'info', 'Hợp đồng sắp hết hạn: Dịch vụ tư vấn IT',
                'Hợp đồng "Dịch vụ tư vấn IT" với nhà cung cấp "Tech Advisors" sẽ hết hạn vào ngày 25/09/2023.', 
                8, 'Contracts', true, date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('-4 days')), date('Y-m-d H:i:s', strtotime('+25 days'))
            ],
            
            // Service notifications
            [
                'service_issue', 'urgent', 'Sự cố dịch vụ: Database Server',
                'Dịch vụ Database Server đang gặp sự cố hiệu suất. Đội kỹ thuật đang khắc phục.', 
                9, 'Services', false, null, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+1 day'))
            ],
            [
                'service_upgrade', 'warning', 'Nâng cấp dịch vụ: Hệ thống Storage',
                'Dịch vụ Storage sẽ được nâng cấp vào ngày 28/08/2023. Có thể xảy ra gián đoạn trong khoảng 30 phút.', 
                10, 'Services', false, null, date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('+7 days'))
            ]
        ];
        
        $insertStmt = $conn->prepare("
            INSERT INTO Notifications 
            (type, severity, title, message, relatedId, relatedTable, isRead, readDate, createdDate, expiryDate) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleNotifications as $notification) {
            $insertStmt->execute($notification);
        }
        
        // Set success message
        $_SESSION['success'] = "Đã tạo các thông báo mẫu để minh họa hệ thống.";
    }
} catch (PDOException $e) {
    // Ignore if table doesn't exist yet
}

// Get all notifications
try {
    // Get filter parameters
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $severity = isset($_GET['severity']) ? $_GET['severity'] : 'all';
    
    // Build SQL query based on filters
    $sql = "SELECT * FROM Notifications WHERE 1=1";
    $params = [];
    
    if ($filter === 'unread') {
        $sql .= " AND isRead = 0";
    } else if ($filter === 'read') {
        $sql .= " AND isRead = 1";
    }
    
    if ($severity !== 'all') {
        $sql .= " AND severity = ?";
        $params[] = $severity;
    }
    
    // Filter by expiry date
    $sql .= " AND (expiryDate IS NULL OR expiryDate > NOW())";
    
    // Order by creation date
    $sql .= " ORDER BY createdDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count notifications by type
    $countStmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN isRead = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN isRead = 1 THEN 1 ELSE 0 END) as read,
            SUM(CASE WHEN severity = 'urgent' THEN 1 ELSE 0 END) as urgent,
            SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) as warning,
            SUM(CASE WHEN severity = 'info' THEN 1 ELSE 0 END) as info
        FROM Notifications
        WHERE expiryDate IS NULL OR expiryDate > NOW()
    ");
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist
    $notifications = [];
    $counts = [
        'total' => 0,
        'unread' => 0,
        'read' => 0,
        'urgent' => 0,
        'warning' => 0,
        'info' => 0
    ];
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Trung tâm thông báo</h1>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <form method="post" action="process_notification.php" class="d-inline">
            <input type="hidden" name="action" value="generate">
            <input type="hidden" name="redirect" value="notifications.php">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>Tạo thông báo mới
            </button>
        </form>
        <?php endif; ?>
    </div>
    
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
    
    <div class="row">
        <!-- Filters and Stats -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="notifications.php">
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="filter" class="form-select">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="unread" <?php echo $filter === 'unread' ? 'selected' : ''; ?>>Chưa đọc</option>
                                <option value="read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mức độ</label>
                            <select name="severity" class="form-select">
                                <option value="all" <?php echo $severity === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="urgent" <?php echo $severity === 'urgent' ? 'selected' : ''; ?>>Khẩn cấp</option>
                                <option value="warning" <?php echo $severity === 'warning' ? 'selected' : ''; ?>>Cảnh báo</option>
                                <option value="info" <?php echo $severity === 'info' ? 'selected' : ''; ?>>Thông tin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tổng số
                            <span class="badge bg-primary rounded-pill"><?php echo $counts['total']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Chưa đọc
                            <span class="badge bg-danger rounded-pill"><?php echo $counts['unread']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Đã đọc
                            <span class="badge bg-secondary rounded-pill"><?php echo $counts['read']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Khẩn cấp
                            <span class="badge bg-danger rounded-pill"><?php echo $counts['urgent']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cảnh báo
                            <span class="badge bg-warning text-dark rounded-pill"><?php echo $counts['warning']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Thông tin
                            <span class="badge bg-info text-dark rounded-pill"><?php echo $counts['info']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Notifications List -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách thông báo</h5>
                    
                    <?php if (!empty($notifications)): ?>
                    <div>
                        <?php if ($filter !== 'read'): ?>
                        <form method="post" action="process_notification.php" class="d-inline">
                            <input type="hidden" name="action" value="mark_all_read">
                            <input type="hidden" name="redirect" value="notifications.php?filter=<?php echo $filter; ?>&severity=<?php echo $severity; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3 text-muted"></i>
                        <p class="mb-0">Không có thông báo nào</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item <?php echo $notification['isRead'] ? '' : 'list-group-item-light'; ?>">
                            <div class="d-flex">
                                <div class="notification-icon <?php echo $notification['severity']; ?> me-3">
                                    <i class="fas <?php echo getNotificationIcon($notification['type']); ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1 <?php echo $notification['isRead'] ? '' : 'fw-bold'; ?>">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                            <?php if (!$notification['isRead']): ?>
                                            <span class="badge <?php echo getNotificationBadgeClass($notification['severity']); ?> ms-2">Mới</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notification['createdDate'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($notification['relatedTable'] === 'Bills'): ?>
                                            <a href="bills.php?id=<?php echo $notification['relatedId']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-eye me-1"></i>Xem hóa đơn
                                            </a>
                                            <?php elseif ($notification['relatedTable'] === 'Contracts'): ?>
                                            <a href="contracts.php?id=<?php echo $notification['relatedId']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-eye me-1"></i>Xem hợp đồng
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$notification['isRead']): ?>
                                            <form method="post" action="process_notification.php" class="d-inline">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <input type="hidden" name="redirect" value="notifications.php?filter=<?php echo $filter; ?>&severity=<?php echo $severity; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-check me-1"></i>Đánh dấu đã đọc
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                        <form method="post" action="process_notification.php" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <input type="hidden" name="redirect" value="notifications.php?filter=<?php echo $filter; ?>&severity=<?php echo $severity; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
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

<?php include 'includes/footer.php'; ?> 