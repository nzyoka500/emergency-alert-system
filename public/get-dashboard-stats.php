<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

try {

    $pdo = getPDO();

    // UPDATED: Table Name 'Alerts'
    $alerts = $pdo->query("SELECT COUNT(*) FROM Alerts")->fetchColumn();

    // UPDATED: Column 'Alerts_status'
    $pending = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='pending'")->fetchColumn();

    $verified = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='verified'")->fetchColumn();

    $resolved = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='resolved'")->fetchColumn();

    // The keys below match the JavaScript 'updateStats' function for the dashboard
    echo json_encode([
        "active_alerts" => $alerts, 
        "pending_alerts" => $pending,
        "verified" => $verified,
        "resolved" => $resolved
    ]);

} catch (Exception $e) {

    echo json_encode([
        "error" => "Failed to load stats"
    ]);

}
?>