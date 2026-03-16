<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_helper.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight "OPTIONS" requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Secure this endpoint
$user = validateApiToken();

try {
    $pdo = getPDO();
    $stmt = $pdo->query("
        SELECT 
            a.id, a.title, a.description, a.status, a.severity,
            a.latitude, a.longitude, a.created_at,
            at.name as category
        FROM alerts a
        JOIN alert_types at ON a.alert_type_id = at.id
        ORDER BY a.created_at DESC
    ");
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $alerts
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}