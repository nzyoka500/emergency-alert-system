<?php
// update-alert.php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$pdo = getPDO();
$id = $_POST['alert_id'];
$title = $_POST['title'];
$desc = $_POST['description'];
$status = $_POST['status'];
$severity = $_POST['severity'] ?? 'Medium';

try {
    $stmt = $pdo->prepare("UPDATE alerts SET title=?, description=?, status=?, severity=? WHERE id=?");
    $stmt->execute([$title, $desc, $status, $severity, $id]);
    echo json_encode(['success' => true, 'message' => 'Alert updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}