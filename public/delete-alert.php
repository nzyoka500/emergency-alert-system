<?php
/**
 * delete-alert.php - Secure AJAX Deletion
 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

// 1. Authorization Check (Only Admin can delete)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

// 2. Input Validation
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode(["success" => false, "message" => "Invalid Alert ID."]);
    exit;
}

try {
    $pdo = getPDO();
    
    // Check if alert exists
    $check = $pdo->prepare("SELECT id FROM alerts WHERE id = ?");
    $check->execute([$id]);
    
    if (!$check->fetch()) {
        throw new Exception("Alert not found or already deleted.");
    }

    // 3. Execution
    $stmt = $pdo->prepare("DELETE FROM alerts WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        "success" => true,
        "message" => "Incident report #$id has been purged from the system."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Server Error: " . $e->getMessage()
    ]);
}

?>