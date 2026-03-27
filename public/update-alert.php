<?php
// update-alert.php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Only logged-in users (usually Admins) should reach this
if (empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getPDO();

// Inputs from the modal form
$id = $_POST['alert_id'] ?? null;
$title = $_POST['title'] ?? '';
$desc = $_POST['description'] ?? '';
$status = $_POST['status'] ?? 'pending';
$severity = $_POST['severity'] ?? 'Medium';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing Alert ID']);
    exit;
}

try {
    // UPDATED: Table 'Alerts' and Column prefixes/desc suffix
    $stmt = $pdo->prepare("
        UPDATE Alerts 
        SET Alerts_title = ?, 
            Alerts_desc = ?, 
            Alerts_status = ?, 
            Alerts_severity = ? 
        WHERE Alerts_id = ?
    ");
    
    $stmt->execute([$title, $desc, $status, $severity, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Incident report updated successfully']);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>