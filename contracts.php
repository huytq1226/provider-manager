<?php
require_once 'includes/init.php';

// Get all providers and services for dropdowns
$stmt = $conn->query("SELECT id, name FROM Providers ORDER BY name");
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get services based on selected provider
$serviceQuery = "SELECT DISTINCT s.id, s.name 
                 FROM Services s 
                 INNER JOIN ProvideService ps ON s.id = ps.serviceId";
if (isset($_GET['providerId']) && !empty($_GET['providerId'])) {
    $serviceQuery .= " WHERE ps.providerId = ?";
}
$serviceQuery .= " ORDER BY s.name";

$stmt = $conn->prepare($serviceQuery);
if (isset($_GET['providerId']) && !empty($_GET['providerId'])) {
    $stmt->execute([(int)$_GET['providerId']]);
} else {
    $stmt->execute();
}
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);


// $stmt = $conn->query("SELECT id, name FROM Services ORDER BY name");
// $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get contracts based on filters
$where = [];
$params = [];

if (isset($_GET['providerId']) && !empty($_GET['providerId'])) {
    $where[] = "providerId = ?";
    $params[] = (int)$_GET['providerId'];
}

if (isset($_GET['serviceId']) && !empty($_GET['serviceId'])) {
    $where[] = "serviceId = ?";
    $params[] = (int)$_GET['serviceId'];
}

$sql = "SELECT c.*, p.name as providerName, s.name as serviceName 
        FROM Contracts c 
        LEFT JOIN Providers p ON c.providerId = p.id 
        LEFT JOIN Services s ON c.serviceId = s.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.signedDate DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">View Contracts</h2>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Filter by Provider</label>
                                    <select class="form-select" name="providerId">
                                        <option value="">All Providers</option>
                                        <?php foreach ($providers as $provider): ?>
                                            <option value="<?php echo $provider['id']; ?>"
                                                <?php echo (isset($_GET['providerId']) && $_GET['providerId'] == $provider['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($provider['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Filter by Service</label>
                                    <select class="form-select" name="serviceId">
                                        <option value="">All Services</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>"
                                                <?php echo (isset($_GET['serviceId']) && $_GET['serviceId'] == $service['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Search by Contract Name</label>
                                    <input type="text" class="form-control" name="contractName" value="<?php echo isset($_GET['contractName']) ? htmlspecialchars($_GET['contractName']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Contracts Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Provider</th>
                                    <th>Service</th>
                                    <th>Signed Date</th>
                                    <th>Expired Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contract['id']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['status']); ?></td>
                                        <td>
                                            <?php
                                            echo htmlspecialchars($contract['price']) . ' ' .
                                                htmlspecialchars($contract['currency']) . ' / ' .
                                                htmlspecialchars($contract['unit']);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($contract['providerName']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['serviceName']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['signedDate']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['expiredDate']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>