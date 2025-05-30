<?php
require_once 'includes/init.php';

// Xử lý thêm mới hoặc chỉnh sửa hợp đồng khi admin submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Kiểm tra dữ liệu đầu vào
                $requiredFields = ['name', 'status', 'price', 'currency', 'unit', 'providerId', 'serviceId', 'signedDate', 'expiredDate', 'nameA', 'phoneA', 'nameB', 'phoneB'];
                $error = false;
                $errorMessage = "";
                
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        $error = true;
                        $errorMessage = "Trường {$field} không được để trống";
                        break;
                    }
                }
                
                if (!$error) {
                    try {
                        // Chuẩn hóa định dạng ngày
                        $signedDate = sanitize($_POST['signedDate']);
                        $expiredDate = sanitize($_POST['expiredDate']);
                        
                        // Đảm bảo định dạng ngày là Y-m-d
                        try {
                            $signedDateObj = new DateTime($signedDate);
                            $expiredDateObj = new DateTime($expiredDate);
                            
                            $signedDate = $signedDateObj->format('Y-m-d');
                            $expiredDate = $expiredDateObj->format('Y-m-d');
                        } catch (Exception $e) {
                            // Nếu không thể chuyển đổi, sử dụng giá trị gốc
                        }
                        
                        $stmt = $conn->prepare("INSERT INTO Contracts (name, status, price, currency, unit, signedDate, expiredDate, nameA, phoneA, nameB, phoneB, providerId, serviceId) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $result = $stmt->execute([
                            sanitize($_POST['name']),
                            sanitize($_POST['status']),
                            (float)$_POST['price'],
                            sanitize($_POST['currency']),
                            sanitize($_POST['unit']),
                            $signedDate,
                            $expiredDate,
                            sanitize($_POST['nameA']),
                            sanitize($_POST['phoneA']),
                            sanitize($_POST['nameB']),
                            sanitize($_POST['phoneB']),
                            (int)$_POST['providerId'],
                            (int)$_POST['serviceId']
                        ]);
                        
                        if ($result) {
                            $_SESSION['success'] = "Hợp đồng đã được tạo thành công!";
                        } else {
                            $_SESSION['error'] = "Lỗi khi tạo hợp đồng";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['error'] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = $errorMessage;
                }
                
                // Chuyển hướng để tránh việc gửi lại form khi refresh
                header('Location: contracts.php');
                exit;
                break;
        }
    }
}

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
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title">View Contracts</h2>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContractModal">
                        <i class="fas fa-plus-circle"></i> Thêm hợp đồng mới
                    </button>
                    <?php endif; ?>
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
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contract['id']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $contract['status'] === 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo htmlspecialchars($contract['status']); ?>
                                            </span>
                                        </td>
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
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-primary" onclick="viewContract(<?php echo $contract['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning" onclick="editContract(<?php echo htmlspecialchars(json_encode($contract)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="deleteContract(<?php echo $contract['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php endif; ?>
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

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<!-- Add Contract Modal -->
<div class="modal fade" id="addContractModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="addContractForm">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm hợp đồng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên hợp đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giá <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tiền tệ <span class="text-danger">*</span></label>
                                <select class="form-select" name="currency" required>
                                    <option value="VND">VND</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Đơn vị <span class="text-danger">*</span></label>
                                <select class="form-select" name="unit" required>
                                    <option value="month">month</option>
                                    <option value="year">year</option>
                                    <option value="project">project</option>
                                    <option value="hour">hour</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nhà cung cấp <span class="text-danger">*</span></label>
                                <select class="form-select" name="providerId" id="providerSelect" required>
                                    <option value="">Chọn nhà cung cấp</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?php echo $provider['id']; ?>">
                                            <?php echo htmlspecialchars($provider['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dịch vụ <span class="text-danger">*</span></label>
                                <select class="form-select" name="serviceId" id="serviceSelect" required>
                                    <option value="">Chọn dịch vụ</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày ký <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="signedDate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày hết hạn <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="expiredDate" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên người đại diện A <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nameA" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại người đại diện A <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phoneA" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên người đại diện B <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nameB" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại người đại diện B <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phoneB" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL Hợp đồng</label>
                        <input type="url" class="form-control" name="contractUrl" placeholder="https://">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm hợp đồng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script xử lý load dịch vụ theo nhà cung cấp -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('providerSelect');
    const serviceSelect = document.getElementById('serviceSelect');
    
    if (providerSelect && serviceSelect) {
        providerSelect.addEventListener('change', function() {
            const providerId = this.value;
            
            // Reset service dropdown
            serviceSelect.innerHTML = '<option value="">Chọn dịch vụ</option>';
            
            if (providerId) {
                // Fetch services for the selected provider
                fetch(`services.php?providerId=${providerId}&format=json`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.services && data.services.length > 0) {
                            // Add services to dropdown
                            data.services.forEach(service => {
                                const option = document.createElement('option');
                                option.value = service.id;
                                option.textContent = service.name;
                                serviceSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching services:', error);
                        
                        // Alternative approach if fetch fails - use predefined services
                        // This is a fallback in case the JSON endpoint doesn't exist
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', `contracts.php?providerId=${providerId}`, true);
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                // Parse the services manually from the HTML
                                // This is a simplified approach and might need adjustments
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = xhr.responseText;
                                const serviceOptions = tempDiv.querySelectorAll('select[name="serviceId"] option');
                                
                                serviceOptions.forEach(option => {
                                    if (option.value !== '') {
                                        const newOption = document.createElement('option');
                                        newOption.value = option.value;
                                        newOption.textContent = option.textContent;
                                        serviceSelect.appendChild(newOption);
                                    }
                                });
                            }
                        };
                        xhr.send();
                    });
            }
        });
    }
    
    // Thêm validation cho form
    const addContractForm = document.getElementById('addContractForm');
    if (addContractForm) {
        addContractForm.addEventListener('submit', function(e) {
            const startDate = new Date(this.signedDate.value);
            const endDate = new Date(this.expiredDate.value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('Ngày hết hạn phải sau ngày ký hợp đồng');
                return false;
            }
            
            if (this.providerId.value === '') {
                e.preventDefault();
                alert('Vui lòng chọn nhà cung cấp');
                return false;
            }
            
            if (this.serviceId.value === '') {
                e.preventDefault();
                alert('Vui lòng chọn dịch vụ');
                return false;
            }
        });
    }
});

function viewContract(id) {
    // Redirect to contract detail page
    window.location.href = `contract-details.php?id=${id}`;
}

function editContract(contract) {
    // Implement contract editing later
    console.log('Edit contract:', contract);
    alert('Chức năng chỉnh sửa hợp đồng sẽ được cập nhật sau!');
}

function deleteContract(id) {
    if (confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')) {
        // Implement contract deletion later
        console.log('Delete contract ID:', id);
        alert('Chức năng xóa hợp đồng sẽ được cập nhật sau!');
    }
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>