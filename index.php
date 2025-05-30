<?php 
require_once 'includes/init.php';
include 'includes/header.php'; 
?>
<div class="hero-section text-center py-5 mb-4 fade-in" style="background: linear-gradient(90deg, #e3f2fd 60%, #fff 100%); border-radius: 1rem;">
    <h1 class="display-4 text-gradient mb-3">Provider Management System</h1>
    <p class="lead mb-4">Nền tảng quản lý nhà cung cấp, dịch vụ, hợp đồng, hóa đơn và nhiều hơn nữa – hiện đại, trực quan, bảo mật.</p>
    <a href="providers.php" class="btn btn-primary btn-lg shadow-hover me-2">Khám phá ngay</a>
    <a href="statistics.php" class="btn btn-outline-primary btn-lg shadow-hover me-2">Xem thống kê</a>
    <?php if (isset($notificationCount) && $notificationCount > 0): ?>
    <a href="notifications.php" class="btn btn-danger btn-lg shadow-hover">
        <i class="fas fa-bell me-1"></i> Xem thông báo <span class="badge bg-white text-danger"><?php echo $notificationCount; ?></span>
    </a>
    <?php endif; ?>
</div>
<div class="row g-4">
    <?php if (isset($notificationCount) && $notificationCount > 0): ?>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-bell fa-2x text-danger mb-3"></i>
                <h5 class="card-title">Trung tâm thông báo</h5>
                <p class="card-text">Xem thông báo về hợp đồng sắp hết hạn và hóa đơn chưa thanh toán.</p>
                <a href="notifications.php" class="btn btn-danger btn-sm mt-2">
                    <i class="fas fa-bell me-1"></i> <?php echo $notificationCount; ?> thông báo
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-building fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Quản lý nhà cung cấp</h5>
                <p class="card-text">Thêm, sửa, xóa, tìm kiếm và xếp hạng các nhà cung cấp theo uy tín, ngành nghề, mã số thuế.</p>
                <a href="providers.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-cogs fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Dịch vụ</h5>
                <p class="card-text">Quản lý các dịch vụ cung cấp, liên kết với nhà cung cấp, theo dõi trạng thái hoạt động.</p>
                <a href="services.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-file-contract fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Hợp đồng</h5>
                <p class="card-text">Quản lý hợp đồng, theo dõi trạng thái, thời hạn, đối tác và dịch vụ liên quan.</p>
                <a href="contracts.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice-dollar fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Hóa đơn</h5>
                <p class="card-text">Tạo, quản lý hóa đơn, tìm kiếm, lọc theo trạng thái, hợp đồng liên quan.</p>
                <a href="bills.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Thống kê</h5>
                <p class="card-text">Xem báo cáo, biểu đồ, thống kê hóa đơn, hợp đồng, nhà cung cấp theo thời gian.</p>
                <a href="statistics.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-balance-scale fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">So sánh</h5>
                <p class="card-text">So sánh các nhà cung cấp theo giá, uy tín, dịch vụ cung cấp, hỗ trợ ra quyết định.</p>
                <a href="compare.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-trophy fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Xếp hạng</h5>
                <p class="card-text">Xem bảng xếp hạng nhà cung cấp theo uy tín, số lượng hợp đồng, hóa đơn.</p>
                <a href="ranking.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-hover h-100 fade-in">
            <div class="card-body text-center">
                <i class="fas fa-envelope fa-2x text-gradient mb-3"></i>
                <h5 class="card-title">Gửi email</h5>
                <p class="card-text">Gửi thông báo, trao đổi với nhà cung cấp nhanh chóng, lưu lịch sử gửi nhận.</p>
                <a href="email.php" class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>