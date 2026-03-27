<?php
/**
 * update-user.php - Secure User CRUD Logic
 * Updated to match the specific database naming convention
 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin only check
// Note: $_SESSION['role_id'] is an alias for Users_Roles_id set during login
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getPDO();

// UPDATED: POST keys to match the form input names (Users_ prefix)
$id = $_POST['Users_id'] ?? null;
$full_name = trim($_POST['Users_full_name'] ?? '');
$email = trim($_POST['Users_email'] ?? '');
$phone = trim($_POST['Users_phone'] ?? '');
$role_id = $_POST['Users_Roles_id'] ?? null;
$status = $_POST['Users_status'] ?? 'active';

try {
    if (!$id || !$full_name || !$email) {
        throw new Exception("Required fields (Name and Email) are missing.");
    }

    // UPDATED: Table Name 'Users' and all column names prefixed with 'Users_'
    $stmt = $pdo->prepare("
        UPDATE Users 
        SET Users_full_name = ?, 
            Users_email = ?, 
            Users_phone = ?, 
            Users_Roles_id = ?, 
            Users_status = ? 
        WHERE Users_id = ?
    ");
    
    $stmt->execute([$full_name, $email, $phone, $role_id, $status, $id]);

    if ($stmt->rowCount() >= 0) {
        echo json_encode(['success' => true, 'message' => 'User profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made or user not found.']);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>