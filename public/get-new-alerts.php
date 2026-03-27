<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

try {
    $pdo = getPDO();

    // UPDATED: Table 'Alerts' and columns prefixed with 'Alerts_'
    $stmt = $pdo->query("
        SELECT Alerts_id, Alerts_title, Alerts_created_at
        FROM Alerts
        WHERE Alerts_status = 'pending'
        ORDER BY Alerts_created_at DESC
        LIMIT 5
    ");

    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // This will now return JSON with keys: Alerts_id, Alerts_title, Alerts_created_at
    echo json_encode($alerts);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch notifications"
    ]);
}
?>