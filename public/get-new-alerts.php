<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$pdo = getPDO();

$stmt = $pdo->query("
SELECT id,title,created_at
FROM alerts
WHERE status='pending'
ORDER BY created_at DESC
LIMIT 5
");

$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($alerts);

?>