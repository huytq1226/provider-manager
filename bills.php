<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug function
function debugToFile($data, $label = 'Debug Data') {
    $file = __DIR__ . '/debug.log';
    $output = "[" . date('Y-m-d H:i:s') . "] $label: " . print_r($data, true) . "\n\n";
    file_put_contents($file, $output, FILE_APPEND);
    return true;
}

// Handle AJAX form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Debug the incoming request
    debugToFile($_POST, 'POST Data');
    debugToFile($_SERVER, 'SERVER Data');
    
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Unknown error occurred'];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    // Validate required fields
                    $requiredFields = ['name', 'des', 'status', 'quantity', 'vat', 'refContractId'];
                    foreach ($requiredFields as $field) {
                        if (!isset($_POST[$field]) || empty($_POST[$field])) {
                            throw new Exception("Field '$field' is required");
                        }
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO Bills (name, des, status, quantity, vat, refContractId) VALUES (?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        sanitize($_POST['name']),
                        sanitize($_POST['des']),
                        sanitize($_POST['status']),
                        (int)$_POST['quantity'],
                        (float)$_POST['vat'],
                        sanitize($_POST['refContractId'])
                    ]);
                    
                    if ($result) {
                        $response = [
                            'success' => true, 
                            'message' => 'Bill created successfully!',
                            'billId' => $conn->lastInsertId()
                        ];
                    } else {
                        throw new Exception("Failed to insert record");
                    }
                } catch (Exception $e) {
                    $response = [
                        'success' => false, 
                        'message' => 'Error: ' . $e->getMessage(),
                        'debug' => [
                            'post_data' => $_POST,
                            'error_info' => $stmt->errorInfo() ?? null
                        ]
                    ];
                }
                break;
            
            case 'search':
                try {
                    $search = isset($_POST['search']) ? '%' . sanitize($_POST['search']) . '%' : '%';
                    $status = isset($_POST['status']) && !empty($_POST['status']) ? sanitize($_POST['status']) : '%';
                    
                    $stmt = $conn->prepare("SELECT b.*, c.name as contractName 
                                           FROM Bills b 
                                           LEFT JOIN Contracts c ON b.refContractId = c.id 
                                           WHERE (b.name LIKE ? OR b.des LIKE ?) 
                                           AND b.status LIKE ? 
                                           ORDER BY b.createdDate DESC");
                    $stmt->execute([$search, $search, $status]);
                    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    ob_start();
                    if (count($bills) > 0) {
                        include 'includes/bill_results_table.php';
                    } else {
                        echo '<div class="alert alert-info">No bills found matching your search criteria.</div>';
                    }
                    $html = ob_get_clean();
                    
                    $response = [
                        'success' => true,
                        'html' => $html,
                        'count' => count($bills)
                    ];
                } catch (Exception $e) {
                    $response = [
                        'success' => false, 
                        'message' => 'Search error: ' . $e->getMessage(),
                        'debug' => [
                            'post_data' => $_POST,
                            'error_info' => $stmt->errorInfo() ?? null
                        ]
                    ];
                }
                break;
        }
    }
    
    echo json_encode($response);
    exit;
}

// Handle regular form submissions (non-AJAX fallback)
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
                redirect('bills.php?success=1');
                break;
        }
    }
}

// Get all contracts for dropdown
$stmt = $conn->query("SELECT id, name FROM Contracts ORDER BY name");
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initial search results (for non-AJAX fallback)
$searchResults = [];
if (isset($_GET['search']) || isset($_GET['status'])) {
    $search = isset($_GET['search']) ? '%' . sanitize($_GET['search']) . '%' : '%';
    $status = isset($_GET['status']) && !empty($_GET['status']) ? sanitize($_GET['status']) : '%';
    
    $stmt = $conn->prepare("SELECT b.*, c.name as contractName 
                           FROM Bills b 
                           LEFT JOIN Contracts c ON b.refContractId = c.id 
                           WHERE (b.name LIKE ? OR b.des LIKE ?) 
                           AND b.status LIKE ? 
                           ORDER BY b.createdDate DESC");
    $stmt->execute([$search, $search, $status]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Create a bill_results_table.php in includes folder -->
<?php
// Create the includes directory if it doesn't exist
if (!file_exists('includes/bill_results_table.php')) {
    $billResultsTable = '<?php if (!empty($bills)): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Contract</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Created Date</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bills as $bill): ?>
            <tr>
                <td><?php echo htmlspecialchars($bill["id"]); ?></td>
                <td><?php echo htmlspecialchars($bill["name"]); ?></td>
                <td><?php echo htmlspecialchars($bill["des"]); ?></td>
                <td><?php echo htmlspecialchars($bill["contractName"] ?? "N/A"); ?></td>
                <td>
                    <span class="badge bg-<?php 
                        echo $bill["status"] === "Paid" ? "success" : 
                            ($bill["status"] === "Pending" ? "warning" : "danger"); 
                    ?>">
                        <?php echo htmlspecialchars($bill["status"]); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($bill["quantity"]); ?></td>
                <td><?php echo htmlspecialchars($bill["createdDate"]); ?></td>
                <td><?php echo htmlspecialchars($bill["vat"]); ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>';
    file_put_contents('includes/bill_results_table.php', $billResultsTable);
}
?>

<!-- Main Content -->
<div class="container-fluid py-4">
    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display:none;">
        <span id="successMessage">Bill created successfully!</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Bill created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Create Bill Form -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title h5 mb-0">Create New Bill</h3>
                </div>
                <div class="card-body">
                    <form id="createBillForm" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="ajax" value="1">
                        
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="quantity" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">VAT (%)</label>
                                <input type="number" class="form-control" name="vat" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Bill</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search Bills -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title h5 mb-0">Search Bills</h3>
                    <span id="resultCount" class="badge bg-light text-dark"><?php echo count($searchResults); ?> results</span>
                </div>
                <div class="card-body">
                    <form id="searchBillForm" method="GET" class="mb-4">
                        <input type="hidden" name="ajax" value="1">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                           placeholder="Search by name or description">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Paid" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </form>

                    <div id="searchResults">
                        <?php 
                        $bills = $searchResults;
                        if (!empty($bills)) {
                            include 'includes/bill_results_table.php';
                        } elseif (isset($_GET['search']) || isset($_GET['status'])) {
                            echo '<div class="alert alert-info">No bills found matching your search criteria.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Bill Form AJAX
    const createBillForm = document.getElementById('createBillForm');
    if (createBillForm) {
        createBillForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable the submit button to prevent multiple submissions
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            const formData = new FormData(this);
            
            // Log the form data for debugging
            console.log('Submitting form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            fetch('bills.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    try {
                        // Try to parse as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // If not valid JSON, throw error with the response text
                        console.error('Invalid JSON response:', text);
                        throw new Error('Server returned invalid JSON response');
                    }
                });
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Show success message - safely check for elements first
                    const successAlert = document.getElementById('successAlert');
                    const successMessage = document.getElementById('successMessage');
                    
                    if (successAlert && successMessage) {
                        successMessage.textContent = data.message;
                        successAlert.style.display = 'block';
                        successAlert.classList.add('show');
                    } else {
                        // Fallback if elements don't exist
                        alert('Success: ' + data.message);
                    }
                    
                    // Reset form
                    createBillForm.reset();
                    
                    // Refresh search results if they exist
                    if (document.getElementById('searchResults')) {
                        refreshSearchResults();
                    }
                } else {
                    // Show error with details if available
                    let errorMsg = data.message || 'Unknown error occurred';
                    if (data.debug) {
                        console.error('Error debug info:', data.debug);
                    }
                    alert('Error: ' + errorMsg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request: ' + error.message);
            })
            .finally(() => {
                // Re-enable the submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
    
    // Search Bills Form AJAX
    const searchBillForm = document.getElementById('searchBillForm');
    if (searchBillForm) {
        searchBillForm.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshSearchResults();
        });
    }
    
    function refreshSearchResults() {
        const searchResultsElement = document.getElementById('searchResults');
        const resultCountElement = document.getElementById('resultCount');
        
        if (!searchResultsElement) {
            console.error('Search results element not found');
            return;
        }
        
        // Show loading indicator
        searchResultsElement.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        const formData = new FormData(searchBillForm);
        formData.append('action', 'search');
        formData.append('ajax', '1');
        
        // Log search parameters
        console.log('Search parameters:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        fetch('bills.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Search response status:', response.status);
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON in search response:', text);
                    throw new Error('Server returned invalid search response');
                }
            });
        })
        .then(data => {
            console.log('Search response data:', data);
            
            if (data.success) {
                searchResultsElement.innerHTML = data.html;
                if (resultCountElement) {
                    resultCountElement.textContent = data.count + ' results';
                }
                
                // Update URL with search parameters without reloading
                const searchParams = new URLSearchParams(formData);
                searchParams.delete('action');
                searchParams.delete('ajax');
                const newUrl = window.location.pathname + '?' + searchParams.toString();
                history.pushState({}, '', newUrl);
            } else {
                searchResultsElement.innerHTML = '<div class="alert alert-danger">Search failed: ' + data.message + '</div>';
                console.error('Search error:', data.message, data.debug);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            if (searchResultsElement) {
                searchResultsElement.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            }
        });
    }
    
    // Auto-hide success alerts after 5 seconds - safely check for bootstrap first
    const alerts = document.querySelectorAll('.alert-success');
    if (typeof bootstrap !== 'undefined') {
        alerts.forEach(alert => {
            setTimeout(() => {
                try {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } catch (e) {
                    console.error('Error closing alert:', e);
                    // Fallback manual hiding
                    alert.style.display = 'none';
                }
            }, 5000);
        });
    } else {
        // Fallback if bootstrap is not available
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?> 