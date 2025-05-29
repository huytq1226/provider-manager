<?php
// Make sure database connection is available
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/notifications.php';
require_once 'includes/auth.php';

// Check if we have a valid database connection
if (!isset($conn) || !$conn) {
    // Redirect with error
    $_SESSION['error'] = "Không thể kết nối đến cơ sở dữ liệu.";
    header("Location: index.php");
    exit;
}

// Process notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $notificationId = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';

    switch ($action) {
        case 'mark_read':
            // Mark a single notification as read
            if ($notificationId > 0) {
                markNotificationAsRead($conn, $notificationId);
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            markAllNotificationsAsRead($conn);
            break;
            
        case 'delete':
            // Delete a notification
            if ($notificationId > 0) {
                deleteNotification($conn, $notificationId);
            }
            break;
            
        case 'generate':
            // Generate notifications (admin only)
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                if (generateNotifications($conn)) {
                    $_SESSION['success'] = "Thông báo đã được tạo thành công.";
                } else {
                    $_SESSION['error'] = "Không thể tạo thông báo. Vui lòng kiểm tra cấu hình cơ sở dữ liệu.";
                }
            }
            break;
    }
    
    // Return JSON for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        $response = [
            'success' => true,
            'unread_count' => countUnreadNotifications($conn)
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect for non-AJAX requests
    header("Location: $redirect");
    exit;
} else {
    // If accessed directly without POST, redirect to home
    header('Location: index.php');
    exit;
}
?> 