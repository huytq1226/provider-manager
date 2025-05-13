<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO Bills (name, des, status, quantity, vat, refContractId) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    sanitize($_POST['name']),
                    sanitize($_POST['des']),
                    sanitize($_POST['status']),
                    (int)$_POST['quantity'],
                    (float)$_POST['vat'],
                    sanitize($_POST['refContractId'])
                ]);
                break;
        }
        redirect('bills.php');
    }
}

// Get all contracts for dropdown
$stmt = $conn->query("SELECT id, name FROM Contracts ORDER BY name");
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Search functionality
$searchResults = [];
if (isset($_GET['search'])) {
    $search = '%' . sanitize($_GET['search']) . '%';
    $status = isset($_GET['status']) ? sanitize($_GET['status']) : '%';
    
    $stmt = $conn->prepare("SELECT * FROM Bills WHERE (name LIKE ? OR des LIKE ?) AND status LIKE ? ORDER BY createdDate DESC");
    $stmt->execute([$search, $search, $status]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <!-- Create Bill Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Bill</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Contract</label>
                            <select class="form-select" name="refContractId" required>
                                <option value="">Select Contract</option>
                                <?php foreach ($contracts as $contract): ?>
                                <option value="<?php echo htmlspecialchars($contract['id']); ?>">
                                    <?php echo htmlspecialchars($contract['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bill Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="des" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VAT (%)</label>
                            <input type="number" class="form-control" name="vat" min="0" max="100" step="0.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Bill</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search Bills -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Search Bills</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                           placeholder="Search by name or description">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Paid" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($searchResults)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Quantity</th>
                                    <th>Created Date</th>
                                    <th>VAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $bill): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bill['id']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['name']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['des']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['status']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['createdDate']); ?></td>
                                    <td><?php echo htmlspecialchars($bill['vat']); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif (isset($_GET['search'])): ?>
                    <div class="alert alert-info">No bills found matching your search criteria.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 