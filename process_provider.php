<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Edit provider
    if ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $taxCode = trim($_POST['taxCode']);
        $vat = trim($_POST['vat']);
        $status = $_POST['status'];
        $address = trim($_POST['address']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $website = trim($_POST['website']);
        $reputation = intval($_POST['reputation']);
        
        // Validate inputs
        if (empty($name) || empty($taxCode) || empty($vat) || empty($address) || empty($email) || empty($phone)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
            header("Location: provider-details.php?id=$id");
            exit;
        }
        
        // Update provider
        $sql = "UPDATE Providers 
                SET name = ?, taxCode = ?, vat = ?, status = ?, address = ?, 
                    email = ?, phone = ?, website = ?, reputation = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $taxCode, $vat, $status, $address, $email, $phone, $website, $reputation, $id]);
        
        $_SESSION['success'] = "Cập nhật nhà cung cấp thành công.";
        header("Location: provider-details.php?id=$id");
        exit;
    }
    
    // Delete provider
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        
        try {
            // Begin transaction to ensure data consistency
            $conn->beginTransaction();
            
            // First delete related records from child tables
            // Delete from ProvideService
            $sql = "DELETE FROM ProvideService WHERE providerId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            
            // Delete Bills related to contracts of this provider
            $sql = "DELETE FROM Bills WHERE refContractId IN (SELECT id FROM Contracts WHERE providerId = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            
            // Delete from Contracts
            $sql = "DELETE FROM Contracts WHERE providerId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            
            // Finally, delete the provider
            $sql = "DELETE FROM Providers WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Xóa nhà cung cấp thành công.";
            header("Location: providers.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            $_SESSION['error'] = "Không thể xóa nhà cung cấp. Lỗi: " . $e->getMessage();
            header("Location: provider-details.php?id=$id");
            exit;
        }
    }
}

// If no valid action, redirect to providers list
header('Location: providers.php');
exit;
?> 