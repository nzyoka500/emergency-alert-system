<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

// In a real production app, check for a Bearer Token here
$pdo = getPDO();

try {
    $stmt = $pdo->query("SELECT a.*, at.name as type_name 
                         FROM alerts a 
                         JOIN alert_types at ON a.alert_type_id = at.id 
                         WHERE a.status != 'resolved' 
                         ORDER BY a.created_at DESC");
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $alerts
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

?>