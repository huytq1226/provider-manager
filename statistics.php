<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get bill statistics by month
$stmt = $conn->query("
    SELECT 
        DATE_FORMAT(createdDate, '%Y-%m') as month,
        COUNT(*) as count
    FROM Bills 
    GROUP BY DATE_FORMAT(createdDate, '%Y-%m')
    ORDER BY month ASC
");
$billStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$months = [];
$counts = [];
foreach ($billStats as $stat) {
    $months[] = $stat['month'];
    $counts[] = $stat['count'];
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Bill Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="billsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Configuration -->
<script>
    const ctx = document.getElementById('billsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Number of Bills',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Number of Bills by Month'
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 