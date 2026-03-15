<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is Admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Role: ' . ($_SESSION['role_id'] ?? 'None')]);
    exit;
}

$pdo = getPDO();
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

try {
    // 1. Get ALL pending alerts for the Notification Bell List
    $listStmt = $pdo->query("
        SELECT a.id, a.title, at.name as category, u.full_name as responder, a.severity, a.created_at
        FROM alerts a
        JOIN alert_types at ON a.alert_type_id = at.id
        JOIN users u ON a.created_by = u.id
        WHERE a.status = 'pending'
        ORDER BY a.id DESC
    ");
    $all_pending = $listStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get only NEW alerts (higher than last_id) for the Pop-up Toast
    $new_alerts = [];
    if ($last_id > 0) {
        foreach ($all_pending as $alert) {
            if ($alert['id'] > $last_id) {
                $new_alerts[] = $alert;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'total_count' => count($all_pending),
        'all_pending' => $all_pending,
        'new_alerts' => $new_alerts
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}