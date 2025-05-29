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

    <style>
        /* Notification Center Styles */
        .notification-dropdown {
            width: 350px;
            max-height: 500px;
            overflow-y: auto;
            padding: 0;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .notification-item {
            border-bottom: 1px solid #f8f9fa;
            padding: 10px 15px;
            transition: background-color 0.2s;
            display: flex;
            align-items: flex-start;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #f0f7ff;
        }
        
        .notification-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        
        .notification-message {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 3px;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #adb5bd;
        }
        
        .notification-actions {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .notification-actions button {
            font-size: 0.8rem;
            padding: 2px 5px;
        }
        
        .no-notifications {
            padding: 30px 15px;
            text-align: center;
            color: #6c757d;
        }
        
        .bell-icon-container {
            position: relative;
        }
        
        .bell-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .notification-icon.urgent {
            background-color: #fee2e2;
            color: #dc3545;
        }
        
        .notification-icon.warning {
            background-color: #fff3cd;
            color: #fd7e14;
        }
        
        .notification-icon.info {
            background-color: #cff4fc;
            color: #0dcaf0;
        }

        /* Notification Toast */
        .notification-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body>
<?php
// Make sure database connection is available
if (!isset($conn) && file_exists('config/database.php')) {
    include_once 'config/database.php';
}

// Include notifications functions
if (file_exists('includes/notifications.php')) {
    include_once 'includes/notifications.php';
    
    // Get unread notifications only if we have a database connection
    $unreadNotifications = [];
    $unreadCount = 0;
    
    if (isset($conn) && $conn) {
        $unreadNotifications = function_exists('getUnreadNotifications') ? getUnreadNotifications($conn) : [];
        $unreadCount = function_exists('countUnreadNotifications') ? countUnreadNotifications($conn) : 0;
    }
} else {
    $unreadNotifications = [];
    $unreadCount = 0;
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
                    <small>Admin User</small>
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
                <li class="nav-item">
                    <a href="/notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        <span>Thông báo</span>
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger float-end"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                        <?php endif; ?>
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
                        <!-- Notification Center -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bell-icon-container">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger bell-badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                                    <?php endif; ?>
                                </div>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                                <div class="notification-header">
                                    <h6 class="mb-0">Thông báo</h6>
                                    <?php if ($unreadCount > 0): ?>
                                    <form method="post" action="process_notification.php">
                                        <input type="hidden" name="action" value="mark_all_read">
                                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <button type="submit" class="btn btn-sm btn-link p-0">Đánh dấu đã đọc tất cả</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (empty($unreadNotifications)): ?>
                                <div class="no-notifications">
                                    <i class="fas fa-bell-slash fa-2x mb-3 text-muted"></i>
                                    <p>Không có thông báo mới</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($unreadNotifications as $notification): ?>
                                    <div class="notification-item unread">
                                        <div class="notification-icon <?php echo $notification['severity']; ?>">
                                            <i class="fas <?php echo getNotificationIcon($notification['type']); ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                            <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                            <div class="notification-time"><?php echo date('d/m/Y H:i', strtotime($notification['createdDate'])); ?></div>
                                            <div class="notification-actions">
                                                <?php if ($notification['relatedTable'] === 'Bills'): ?>
                                                <a href="bills.php?id=<?php echo $notification['relatedId']; ?>" class="btn btn-sm btn-outline-primary">Xem hóa đơn</a>
                                                <?php elseif ($notification['relatedTable'] === 'Contracts'): ?>
                                                <a href="contracts.php?id=<?php echo $notification['relatedId']; ?>" class="btn btn-sm btn-outline-primary">Xem hợp đồng</a>
                                                <?php endif; ?>
                                                
                                                <form method="post" action="process_notification.php" class="d-inline">
                                                    <input type="hidden" name="action" value="mark_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Đánh dấu đã đọc</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="dropdown-divider"></div>
                                <div class="text-center p-2">
                                    <form method="post" action="process_notification.php">
                                        <input type="hidden" name="action" value="generate">
                                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Tạo thông báo mới</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <main class="container-fluid py-4"> 
            
            <!-- Notification Toast Container -->
            <div class="notification-toast" id="notificationToast"></div> 