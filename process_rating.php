<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $providerId = isset($_POST['provider_id']) ? intval($_POST['provider_id']) : 0;
    $contractId = isset($_POST['contract_id']) && !empty($_POST['contract_id']) ? intval($_POST['contract_id']) : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Validate provider ID
    if ($providerId <= 0) {
        $_SESSION['error'] = "Mã nhà cung cấp không hợp lệ.";
        header('Location: providers.php');
        exit;
    }
    
    try {
        // Check if rating tables exist
        $tableCheck = $conn->query("SHOW TABLES LIKE 'RatingCriteria'");
        if ($tableCheck->rowCount() == 0) {
            throw new Exception("Bảng đánh giá chưa được tạo. Vui lòng thiết lập hệ thống đánh giá trước.");
        }
        
        // Get all rating criteria
        $criteriaStmt = $conn->query("SELECT id FROM RatingCriteria WHERE status = 'Active'");
        $criteria = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($criteria)) {
            throw new Exception("Không tìm thấy tiêu chí đánh giá nào trong hệ thống.");
        }
        
        // Validate that we have scores for each criteria
        $hasScores = false;
        foreach ($criteria as $criterion) {
            $criteriaId = $criterion['id'];
            $scoreKey = 'score_' . $criteriaId;
            
            if (isset($_POST[$scoreKey])) {
                $hasScores = true;
                break;
            }
        }
        
        if (!$hasScores) {
            throw new Exception("Vui lòng đánh giá ít nhất một tiêu chí.");
        }
        
        // Start transaction
        $conn->beginTransaction();
        
        // Insert rating record
        $sql = "INSERT INTO ProviderRatings (providerId, contractId, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$providerId, $contractId, $comment]);
        
        // Get the inserted rating ID
        $ratingId = $conn->lastInsertId();
        
        // Insert scores for each criteria
        $scoreCount = 0;
        foreach ($criteria as $criterion) {
            $criteriaId = $criterion['id'];
            $scoreKey = 'score_' . $criteriaId;
            
            if (isset($_POST[$scoreKey])) {
                $score = intval($_POST[$scoreKey]);
                
                // Validate score (1-5)
                if ($score < 1 || $score > 5) {
                    throw new Exception("Điểm đánh giá phải từ 1 đến 5.");
                }
                
                // Insert score
                $scoreSql = "INSERT INTO RatingScores (ratingId, criteriaId, score) VALUES (?, ?, ?)";
                $scoreStmt = $conn->prepare($scoreSql);
                $scoreStmt->execute([$ratingId, $criteriaId, $score]);
                $scoreCount++;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Đánh giá nhà cung cấp thành công với $scoreCount tiêu chí!";
    } catch (Exception $e) {
        // Rollback on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    }
    
    // Redirect back to provider details
    header("Location: provider-details.php?id=$providerId");
    exit;
} else {
    // If not POST request, redirect to providers list
    header('Location: providers.php');
    exit;
}
?> 