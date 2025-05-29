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
                                <span class="badge bg-danger">3</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Notifications</h6>
                                <a class="dropdown-item" href="#">New contract signed</a>
                                <a class="dropdown-item" href="#">Payment received</a>
                                <a class="dropdown-item" href="#">Service update</a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <main class="container-fluid py-4"> 