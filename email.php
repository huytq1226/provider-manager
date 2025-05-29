<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providerEmail = sanitize($_POST['providerEmail']);
    $subject = sanitize($_POST['subject']);
    $content = sanitize($_POST['content']);
    
    // Validate email
    if (!filter_var($providerEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address format.';
        $messageType = 'danger';
    } else {
        // Format email content
        $emailContent = "Date: " . date('Y-m-d H:i:s') . "\n";
        $emailContent .= "To: " . $providerEmail . "\n";
        $emailContent .= "Subject: " . $subject . "\n";
        $emailContent .= "Content:\n" . $content . "\n";
        $emailContent .= "----------------------------------------\n\n";
        
        // Save to file
        $file = 'emails.txt';
        if (file_put_contents($file, $emailContent, FILE_APPEND)) {
            $message = 'Email has been saved successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error saving email.';
            $messageType = 'danger';
        }
    }
}

// Get all providers for dropdown
$stmt = $conn->query("SELECT id, name, email FROM Providers ORDER BY name");
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Gửi Email đến Nhà Cung Cấp</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Provider</label>
                            <select class="form-select" id="providerSelect" onchange="updateEmail()">
                                <option value="">Select a provider</option>
                                <?php foreach ($providers as $provider): ?>
                                <option value="<?php echo htmlspecialchars($provider['email']); ?>">
                                    <?php echo htmlspecialchars($provider['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Provider Email</label>
                            <input type="email" class="form-control" name="providerEmail" id="providerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </form>
                </div>
            </div>

            <!-- View Saved Emails -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Emails đã lưu</h3>
                </div>
                <div class="card-body">
                    <?php
                    $file = 'emails.txt';
                    if (file_exists($file)) {
                        $emails = file_get_contents($file);
                        if (!empty($emails)) {
                            echo '<pre class="bg-light p-3 rounded">' . htmlspecialchars($emails) . '</pre>';
                        } else {
                            echo '<p class="text-muted">No emails have been saved yet.</p>';
                        }
                    } else {
                        echo '<p class="text-muted">No emails have been saved yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateEmail() {
        const select = document.getElementById('providerSelect');
        const emailInput = document.getElementById('providerEmail');
        emailInput.value = select.value;
    }
</script>

<?php include 'includes/footer.php'; ?> 