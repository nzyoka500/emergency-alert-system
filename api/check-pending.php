<?php
/**
 * api/check-pending.php
 * Checks for recently created alerts that are 'pending'
 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

session_start();
if ($_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getPDO();
$last_check = $_GET['last_id'] ?? 0;

try {
    // Fetch pending alerts created after the last ID the admin saw
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, at.name as type, a.created_at, u.full_name as responder
        FROM alerts a
        JOIN alert_types at ON a.alert_type_id = at.id
        JOIN users u ON a.created_by = u.id
        WHERE a.status = 'pending' AND a.id > ?
        ORDER BY a.id DESC
    ");
    $stmt->execute([$last_check]);
    $new_alerts = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($new_alerts),
        'alerts' => $new_alerts
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>