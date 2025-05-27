<?php
session_start(); // Bắt đầu session
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra nếu session role không tồn tại, mặc định là 'user'
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $role === 'admin') { // Chỉ admin được phép thực hiện các hành động
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO Services (name, des, status) VALUES (?, ?, ?)");
                $stmt->execute([
                    sanitize($_POST['name']),
                    sanitize($_POST['des']),
                    sanitize($_POST['status'])
                ]);
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE Services SET name = ?, des = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    sanitize($_POST['name']),
                    sanitize($_POST['des']),
                    sanitize($_POST['status']),
                    (int)$_POST['id']
                ]);
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM Services WHERE id = ?");
                $stmt->execute([(int)$_POST['id']]);
                break;
        }
        redirect('services.php');
    } else {
        // Nếu không phải admin, chuyển hướng hoặc thông báo lỗi
        redirect('services.php?error=unauthorized');
    }
}

// Get all services
$stmt = $conn->query("SELECT * FROM Services ORDER BY id DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Manage Services</h2>
                    <?php if ($role === 'admin'): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        Add New Service
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <?php if ($role === 'admin'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['id']); ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['des']); ?></td>
                                    <td><?php echo htmlspecialchars($service['status']); ?></td>
                                    <?php if ($role === 'admin'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteService(<?php echo $service['id']; ?>)">
                                            Delete
                                        </button>
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

<?php if ($role === 'admin'): ?>
<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="des" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Thêm dịch vụ mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="des" id="edit_des" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
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
<?php endif; ?>

<!-- Delete Service Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
    function editService(service) {
        document.getElementById('edit_id').value = service.id;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_des').value = service.des;
        document.getElementById('edit_status').value = service.status;
        
        new bootstrap.Modal(document.getElementById('editServiceModal')).show();
    }

    function deleteService(id) {
        if (confirm('Are you sure you want to delete this service?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>