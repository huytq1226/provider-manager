<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Xử lý tìm kiếm
$search = trim($_GET['search'] ?? '');
$searchSql = '';
$params = [];
if ($search !== '') {
    $searchSql = "WHERE name LIKE ? OR taxCode LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Lấy top 10 nhà cung cấp theo reputation
$sql = "SELECT * FROM Providers $searchSql ORDER BY reputation DESC, id ASC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$topProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tất cả providers cho admin quản trị
$stmtAll = $conn->query("SELECT * FROM Providers ORDER BY id DESC");
$providers = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-hover fade-in mb-4">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0">BẢNG XẾP HẠNG NHÀ CUNG CẤP UY TÍN NHẤT</h4>
                </div>
                <div class="card-body p-0">
                    <form class="p-3 border-bottom bg-light" method="get" action="">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input type="text" class="form-control" name="search" placeholder="Tìm theo tên hoặc mã số thuế..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                    <div class="ranking-list">
                        <?php foreach ($topProviders as $i => $p): ?>
                        <div class="d-flex align-items-center px-3 py-3 <?php echo $i % 2 ? 'bg-light' : ''; ?>" style="border-bottom:1px solid #eee;">
                            <div class="fw-bold fs-4 me-3 text-danger" style="width:32px;"> <?php echo $i+1; ?> </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-gradient fs-5"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="text-muted small">
                                    <span class="me-3"><i class="fas fa-id-card"></i> <b>MST:</b> <?php echo htmlspecialchars($p['taxCode']); ?></span>
                                    <span><i class="fas fa-industry"></i> <b>Ngành nghề:</b> <?php echo htmlspecialchars($p['des'] ?? $p['status']); ?></span>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> <?php echo $p['reputation']; ?>/5</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($topProviders)): ?>
                        <div class="text-center py-4 text-muted">Không tìm thấy nhà cung cấp phù hợp.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isAdmin()): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Manage Providers</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                        Add New Provider
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Tax Code</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Reputation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($provider['id']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['taxCode']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['email']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['reputation']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editProvider(<?php echo htmlspecialchars(json_encode($provider)); ?>)">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteProvider(<?php echo $provider['id']; ?>)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal và JS quản trị giữ nguyên như cũ -->
    <?php endif; ?>
</div>

<!-- Add Provider Modal -->
<div class="modal fade" id="addProviderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tax Code</label>
                        <input type="text" class="form-control" name="taxCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VAT</label>
                        <input type="text" class="form-control" name="vat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reputation (1-5)</label>
                        <input type="number" class="form-control" name="reputation" min="1" max="5" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="editProviderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tax Code</label>
                        <input type="text" class="form-control" name="taxCode" id="edit_taxCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VAT</label>
                        <input type="text" class="form-control" name="vat" id="edit_vat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="edit_address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website" id="edit_website">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reputation (1-5)</label>
                        <input type="number" class="form-control" name="reputation" id="edit_reputation" min="1" max="5" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Provider Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
    function editProvider(provider) {
        document.getElementById('edit_id').value = provider.id;
        document.getElementById('edit_name').value = provider.name;
        document.getElementById('edit_taxCode').value = provider.taxCode;
        document.getElementById('edit_vat').value = provider.vat;
        document.getElementById('edit_status').value = provider.status;
        document.getElementById('edit_address').value = provider.address;
        document.getElementById('edit_email').value = provider.email;
        document.getElementById('edit_phone').value = provider.phone;
        document.getElementById('edit_website').value = provider.website;
        document.getElementById('edit_reputation').value = provider.reputation;
        
        new bootstrap.Modal(document.getElementById('editProviderModal')).show();
    }

    function deleteProvider(id) {
        if (confirm('Are you sure you want to delete this provider?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php include 'includes/footer.php'; ?> 