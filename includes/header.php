<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Management System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
    // Calculate notifications for the badge
    $notificationCount = 0;
    
    // Only calculate if we're not already on the notifications page
    $currentPage = basename($_SERVER['PHP_SELF']);
    $role = $_SESSION['role'] ?? '';
    // Make sure database connection exists before using it
    if (isset($conn) && $currentPage !== 'notifications.php') {
        try {
            // Calculate date for expiring contracts (next 30 days)
            $today = new DateTime();
            $thirtyDaysLater = clone $today;
            $thirtyDaysLater->modify('+30 days');
            $todayFormatted = $today->format('Y-m-d');
            $thirtyDaysLaterFormatted = $thirtyDaysLater->format('Y-m-d');
            
            // Count expiring contracts
            $contractsStmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM Contracts 
                WHERE status = 'Active' 
                AND expiredDate BETWEEN ? AND ?
            ");
            $contractsStmt->execute([$todayFormatted, $thirtyDaysLaterFormatted]);
            $expiringContractsCount = $contractsStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count pending bills
            $billsStmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM Bills 
                WHERE status = 'Pending'
            ");
            $billsStmt->execute();
            $pendingBillsCount = $billsStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $notificationCount = $expiringContractsCount + $pendingBillsCount;
        } catch (Exception $e) {
            // Silently handle any database errors
            $notificationCount = 0;
        }
    }
    ?>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3 class="text-gradient">Provider Management</h3>
                <button id="sidebarCollapse" class="btn btn-link d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h6 class="mb-0">Welcome</h6>
                    <small><?php echo $role; ?></small>
                </div>
            </div>

            <ul class="list-unstyled components">
                <li class="nav-item">
                    <a href="/" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        <span>Thông báo</span>
                        <?php if ($notificationCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $notificationCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/providers.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Danh sách nhà cung cấp</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/services.php" class="nav-link">
                        <i class="fas fa-cogs"></i>
                        <span>Dịch vụ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/contracts.php" class="nav-link">
                        <i class="fas fa-file-contract"></i>
                        <span>Hợp đồng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/bills.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Hóa đơn</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/compare.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>So sánh</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/statistics.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Thống kê</span>
                    </a>
                </li>
            </ul>
            <ul class="list-unstyled components">

            </ul>   
            <div class="sidebar-footer">
                <!-- <a href="/settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                </a> -->
                <a href="/login.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary d-none d-md-block">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php if ($notificationCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $notificationCount; ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Thông báo</h6>
                                <?php if ($notificationCount > 0): ?>
                                <a class="dropdown-item" href="/notifications.php">Xem <?php echo $notificationCount; ?> thông báo</a>
                                <?php else: ?>
                                <span class="dropdown-item">Không có thông báo mới</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <main class="container-fluid py-4"> 