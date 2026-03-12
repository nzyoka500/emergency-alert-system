<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

try {

    $pdo = getPDO();

    $alerts = $pdo->query("SELECT COUNT(*) FROM alerts")->fetchColumn();

    $pending = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status='pending'")->fetchColumn();

    $verified = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status='verified'")->fetchColumn();

    $resolved = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status='resolved'")->fetchColumn();

    echo json_encode([
        "alerts" => $alerts,
        "pending" => $pending,
        "verified" => $verified,
        "resolved" => $resolved
    ]);

} catch (Exception $e) {

    echo json_encode([
        "error" => "Failed to load stats"
    ]);

}