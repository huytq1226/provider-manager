<?php
/**
 * Notifications handler for the Provider Management System
 * Manages retrieving, counting, and marking notifications
 */

/**
 * Get unread notifications for display
 * 
 * @param PDO $conn Database connection
 * @param int $limit Maximum number of notifications to retrieve
 * @return array Notifications data
 */
function getUnreadNotifications($conn, $limit = 10) {
    // Check if connection is valid
    if (!$conn) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM Notifications 
            WHERE isRead = 0 
            AND (expiryDate IS NULL OR expiryDate > NOW())
            ORDER BY 
                CASE severity 
                    WHEN 'urgent' THEN 1 
                    WHEN 'warning' THEN 2 
                    ELSE 3 
                END, 
                createdDate DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If the table doesn't exist yet, return empty array
        return [];
    }
}

/**
 * Count unread notifications
 * 
 * @param PDO $conn Database connection
 * @return int Number of unread notifications
 */
function countUnreadNotifications($conn) {
    // Check if connection is valid
    if (!$conn) {
        return 0;
    }
    
    try {
        $stmt = $conn->query("
            SELECT COUNT(*) FROM Notifications 
            WHERE isRead = 0 
            AND (expiryDate IS NULL OR expiryDate > NOW())
        ");
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        // If the table doesn't exist yet, return 0
        return 0;
    }
}

/**
 * Mark a notification as read
 * 
 * @param PDO $conn Database connection
 * @param int $id Notification ID
 * @return bool Success status
 */
function markNotificationAsRead($conn, $id) {
    // Check if connection is valid
    if (!$conn) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE Notifications 
            SET isRead = 1, readDate = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mark all notifications as read
 * 
 * @param PDO $conn Database connection
 * @return bool Success status
 */
function markAllNotificationsAsRead($conn) {
    // Check if connection is valid
    if (!$conn) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE Notifications 
            SET isRead = 1, readDate = NOW() 
            WHERE isRead = 0
        ");
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Force generate notifications (for testing or manual update)
 * 
 * @param PDO $conn Database connection
 * @return bool Success status
 */
function generateNotifications($conn) {
    // Check if connection is valid
    if (!$conn) {
        return false;
    }
    
    try {
        // Check if the procedures exist
        $stmt = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GenerateBillDueNotifications'");
        if ($stmt->rowCount() > 0) {
            $conn->query("CALL GenerateBillDueNotifications()");
            $conn->query("CALL GenerateContractExpiringNotifications()");
            return true;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete a notification
 * 
 * @param PDO $conn Database connection
 * @param int $id Notification ID
 * @return bool Success status
 */
function deleteNotification($conn, $id) {
    // Check if connection is valid
    if (!$conn) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM Notifications WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get notification badge color based on severity
 * 
 * @param string $severity Notification severity
 * @return string CSS class for badge
 */
function getNotificationBadgeClass($severity) {
    switch ($severity) {
        case 'urgent':
            return 'bg-danger';
        case 'warning':
            return 'bg-warning text-dark';
        case 'info':
        default:
            return 'bg-info text-dark';
    }
}

/**
 * Get notification icon based on type
 * 
 * @param string $type Notification type
 * @return string FontAwesome icon class
 */
function getNotificationIcon($type) {
    switch ($type) {
        case 'bill_due':
            return 'fa-file-invoice-dollar';
        case 'contract_expiring':
            return 'fa-file-contract';
        case 'service_issue':
            return 'fa-exclamation-triangle';
        case 'service_upgrade':
            return 'fa-arrow-circle-up';
        case 'provider_update':
            return 'fa-building';
        case 'system':
            return 'fa-cogs';
        default:
            return 'fa-bell';
    }
}
?> 