<?php
/**
 * update-user.php - Secure User CRUD Logic
 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

session_start();
if ($_SESSION['role_id'] != 1) { // Admin only
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getPDO();

$id = $_POST['user_id'] ?? null;
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role_id = $_POST['role_id'] ?? null;
$status = $_POST['status'] ?? 'active';

try {
    if (!$id || !$full_name || !$email) throw new Exception("Required fields missing.");

    $stmt = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, email = ?, phone = ?, role_id = ?, status = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([$full_name, $email, $phone, $role_id, $status, $id]);

    echo json_encode(['success' => true, 'message' => 'User profile updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}