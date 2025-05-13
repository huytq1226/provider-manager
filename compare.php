<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all services for dropdown
$stmt = $conn->query("SELECT id, name FROM Services ORDER BY name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get providers for selected service
$providers = [];
if (isset($_GET['serviceId']) && !empty($_GET['serviceId'])) {
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.name,
            p.reputation,
            ps.providePrice,
            ps.currency,
            ps.unit
        FROM Providers p
        JOIN ProvideService ps ON p.id = ps.providerId
        WHERE ps.serviceId = ?
        ORDER BY ps.providePrice ASC
    ");
    $stmt->execute([(int)$_GET['serviceId']]);
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Compare Providers</h2>
                </div>
                <div class="card-body">
                    <!-- Service Selection Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Service</label>
                                    <select class="form-select" name="serviceId" onchange="this.form.submit()">
                                        <option value="">Select a service</option>
                                        <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>"
                                                <?php echo (isset($_GET['serviceId']) && $_GET['serviceId'] == $service['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($providers)): ?>
                    <!-- Providers Comparison Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Provider Name</th>
                                    <th>Price</th>
                                    <th>Reputation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($provider['providePrice']) . ' ' . 
                                             htmlspecialchars($provider['currency']) . ' / ' . 
                                             htmlspecialchars($provider['unit']); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $stars = '';
                                        for ($i = 1; $i <= 5; $i++) {
                                            $stars .= $i <= $provider['reputation'] ? '★' : '☆';
                                        }
                                        echo $stars . ' (' . $provider['reputation'] . '/5)';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif (isset($_GET['serviceId'])): ?>
                    <div class="alert alert-info">
                        No providers found for the selected service.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 