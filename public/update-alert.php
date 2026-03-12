<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$pdo = getPDO();
$id = $_POST['alert_id'] ?? null;
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';

try {
    $stmt = $pdo->prepare("UPDATE alerts SET title = ?, description = ?, status = ? WHERE id = ?");
    $stmt->execute([$title, $description, $status, $id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Alert updated successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Update failed: ' . $e->getMessage()
    ]);
}

?>