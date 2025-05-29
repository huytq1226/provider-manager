<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize arrays to prevent errors if queries fail
$billStats = [];
$months = [];
$counts = [];
$paidCounts = [];
$pendingCounts = [];
$providerStats = [];
$serviceStats = [];
$contractMonths = [];
$contractValues = [];
$summaryStats = [
    'provider_count' => 0,
    'service_count' => 0,
    'contract_count' => 0,
    'bill_count' => 0,
    'paid_bill_count' => 0
];
$avgReputationPercentage = 0;

try {
    // Get bill statistics by month
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(createdDate, '%Y-%m') as month,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count
        FROM Bills 
        WHERE createdDate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(createdDate, '%Y-%m')
        ORDER BY month ASC
    ");
    $billStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for Chart.js
    foreach ($billStats as $stat) {
        // Format month for display (YYYY-MM to MMM YYYY)
        $date = DateTime::createFromFormat('Y-m', $stat['month']);
        $formattedMonth = $date ? $date->format('M Y') : $stat['month'];
        
        $months[] = $formattedMonth;
        $counts[] = $stat['count'];
        $paidCounts[] = $stat['paid_count'];
        $pendingCounts[] = $stat['pending_count'];
    }

    // Get provider statistics
    $stmt = $conn->query("
        SELECT 
            p.name,
            COUNT(c.id) as contract_count,
            AVG(c.price) as avg_contract_value
        FROM Providers p
        LEFT JOIN Contracts c ON p.id = c.providerId
        GROUP BY p.id
        ORDER BY contract_count DESC, avg_contract_value DESC
        LIMIT 5
    ");
    $providerStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get service statistics
    $stmt = $conn->query("
        SELECT 
            s.name,
            COUNT(ps.providerId) as provider_count
        FROM Services s
        LEFT JOIN ProvideService ps ON s.id = ps.serviceId
        GROUP BY s.id
        ORDER BY provider_count DESC
        LIMIT 5
    ");
    $serviceStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get overall summary statistics
    $stmt = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM Providers) as provider_count,
            (SELECT COUNT(*) FROM Services) as service_count,
            (SELECT COUNT(*) FROM Contracts) as contract_count,
            (SELECT COUNT(*) FROM Bills) as bill_count,
            (SELECT COUNT(*) FROM Bills WHERE status = 'Paid') as paid_bill_count
    ");
    $summaryStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate percentage of paid bills
    $paidPercentage = 0;
    if ($summaryStats['bill_count'] > 0) {
        $paidPercentage = round(($summaryStats['paid_bill_count'] / $summaryStats['bill_count']) * 100);
    }

    // Get average provider reputation
    $stmt = $conn->query("SELECT AVG(reputation) as avg_reputation FROM Providers");
    $avgReputation = $stmt->fetch(PDO::FETCH_ASSOC)['avg_reputation'];
    $avgReputationPercentage = round($avgReputation);

    // Get monthly contract value trend
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(signedDate, '%Y-%m') as month,
            SUM(price) as total_value
        FROM Contracts 
        WHERE signedDate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(signedDate, '%Y-%m')
        ORDER BY month ASC
    ");
    $contractValueStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contractValueStats as $stat) {
        $date = DateTime::createFromFormat('Y-m', $stat['month']);
        $formattedMonth = $date ? $date->format('M Y') : $stat['month'];
        
        $contractMonths[] = $formattedMonth;
        $contractValues[] = $stat['total_value'];
    }
} catch (PDOException $e) {
    // Log error (don't display to users)
    error_log("Database error in statistics.php: " . $e->getMessage());
    // Set error flag to display message to user
    $dbError = true;
}

include 'includes/header.php';
?>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.stat-card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.stat-title {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 1.5rem;
}

.stat-trend {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.trend-up {
    color: #28a745;
}

.trend-down {
    color: #dc3545;
}

.card-header-tabs {
    margin-right: -1.25rem;
    margin-bottom: -0.75rem;
    margin-left: -1.25rem;
    border-bottom: 0;
}

.progress-thin {
    height: 8px;
    border-radius: 4px;
}

.data-table th {
    font-weight: 600;
    color: #495057;
}

.data-table td, .data-table th {
    padding: 0.75rem 1rem;
}

.widget-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.2rem;
    color: #344767;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #344767;
    position: relative;
    padding-bottom: 0.5rem;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #4e73df, #36b9cc);
    border-radius: 3px;
}

.bg-gradient-primary {
    background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
}

.bg-gradient-success {
    background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
}

.bg-gradient-info {
    background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
}

.bg-gradient-warning {
    background: linear-gradient(87deg, #fb6340 0, #fbb140 100%);
}

.text-xs {
    font-size: 0.75rem;
}

.text-uppercase {
    text-transform: uppercase;
}

.font-weight-bold {
    font-weight: 700;
}

.text-muted {
    color: #8898aa !important;
}
</style>

<!-- Main Content -->
<div class="container-fluid py-4">
    <h1 class="section-title">Thống kê tổng quan</h1>
    
    <?php if (isset($dbError)): ?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-exclamation-triangle"></i> Lỗi dữ liệu</h4>
        <p>Có lỗi xảy ra khi tải dữ liệu thống kê. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
    </div>
    <?php endif; ?>
    
    <!-- Summary Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng nhà cung cấp</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($summaryStats['provider_count']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng dịch vụ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($summaryStats['service_count']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng hợp đồng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($summaryStats['contract_count']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tỷ lệ hóa đơn đã thanh toán</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $paidPercentage; ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-thin">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $paidPercentage; ?>%" aria-valuenow="<?php echo $paidPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Bill Statistics Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê hóa đơn theo tháng</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($months)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu hóa đơn trong 12 tháng qua.
                    </div>
                    <?php else: ?>
                    <div class="chart-container">
                        <canvas id="billsChart"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Average Reputation Gauge -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Điểm uy tín trung bình nhà cung cấp</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="reputationChart"></canvas>
                    </div>
                    <div class="text-center mt-3">
                        <h4><?php echo number_format($avgReputationPercentage, 1); ?>/100</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Contract Value Trend -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Xu hướng giá trị hợp đồng theo tháng</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($contractMonths)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu hợp đồng trong 12 tháng qua.
                    </div>
                    <?php else: ?>
                    <div class="chart-container">
                        <canvas id="contractValueChart"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Services Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bố dịch vụ</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($serviceStats)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu về dịch vụ.
                    </div>
                    <?php else: ?>
                    <div class="chart-container">
                        <canvas id="servicesChart"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Providers Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nhà cung cấp hàng đầu</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($providerStats)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu về nhà cung cấp.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered data-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nhà cung cấp</th>
                                    <th>Số hợp đồng</th>
                                    <th>Giá trị trung bình</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($providerStats as $provider): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                    <td><?php echo number_format($provider['contract_count']); ?></td>
                                    <td><?php echo number_format($provider['avg_contract_value'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Services Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow stat-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dịch vụ phổ biến nhất</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($serviceStats)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu về dịch vụ.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered data-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Dịch vụ</th>
                                    <th>Số nhà cung cấp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($serviceStats as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo number_format($service['provider_count']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Configuration -->
<script>
    // Set global Chart.js options
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font = Chart.defaults.font || {};
        Chart.defaults.font.family = "'Nunito', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = "#858796";
        
        // Bills Chart
        const billsChartElement = document.getElementById('billsChart');
        if (billsChartElement) {
            const billsCtx = billsChartElement.getContext('2d');
            new Chart(billsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [
                        {
                            label: 'Tổng hóa đơn',
                            data: <?php echo json_encode($counts); ?>,
                            backgroundColor: 'rgba(78, 115, 223, 0.7)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Đã thanh toán',
                            data: <?php echo json_encode($paidCounts); ?>,
                            backgroundColor: 'rgba(28, 200, 138, 0.7)',
                            borderColor: 'rgba(28, 200, 138, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Chưa thanh toán',
                            data: <?php echo json_encode($pendingCounts); ?>,
                            backgroundColor: 'rgba(246, 194, 62, 0.7)',
                            borderColor: 'rgba(246, 194, 62, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                drawBorder: false,
                                color: "rgba(0, 0, 0, 0.05)"
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFont: {
                                size: 14
                            },
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10
                        }
                    }
                }
            });
        }
        
        // Reputation Gauge Chart
        const reputationChartElement = document.getElementById('reputationChart');
        if (reputationChartElement) {
            const reputationCtx = reputationChartElement.getContext('2d');
            new Chart(reputationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Điểm uy tín', 'Còn lại'],
                    datasets: [{
                        data: [<?php echo $avgReputationPercentage; ?>, 100 - <?php echo $avgReputationPercentage; ?>],
                        backgroundColor: [
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(236, 236, 236, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                }
            });
        }
        
        // Contract Value Chart
        const contractValueChartElement = document.getElementById('contractValueChart');
        if (contractValueChartElement) {
            const contractValueCtx = contractValueChartElement.getContext('2d');
            new Chart(contractValueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($contractMonths); ?>,
                    datasets: [{
                        label: 'Giá trị hợp đồng',
                        data: <?php echo json_encode($contractValues); ?>,
                        fill: true,
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: '#fff',
                        pointHitRadius: 10,
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: "rgba(0, 0, 0, 0.05)"
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFont: {
                                size: 14
                            },
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function(context) {
                                    var value = context.parsed.y;
                                    return 'Giá trị: ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Services Chart
        const servicesChartElement = document.getElementById('servicesChart');
        if (servicesChartElement && <?php echo count($serviceStats) > 0 ? 'true' : 'false'; ?>) {
            const servicesCtx = servicesChartElement.getContext('2d');
            new Chart(servicesCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php 
                        foreach ($serviceStats as $service) {
                            echo "'" . htmlspecialchars($service['name']) . "',";
                        }
                        ?>
                    ],
                    datasets: [{
                        data: [
                            <?php 
                            foreach ($serviceStats as $service) {
                                echo $service['provider_count'] . ',';
                            }
                            ?>
                        ],
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        }
    } else {
        console.error('Chart.js library not loaded');
        document.querySelectorAll('.chart-container').forEach(function(container) {
            container.innerHTML = '<div class="alert alert-warning">Không thể tải biểu đồ. Vui lòng tải lại trang.</div>';
        });
    }
</script>

<?php include 'includes/footer.php'; ?> 