<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_helper.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight "OPTIONS" requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$user = validateApiToken();

// Only Responders (2) or Admins (1) can create alerts
if ($user['role_id'] > 2) {
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO alerts (alert_type_id, title, description, latitude, longitude, severity, created_by, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([
        $data['type_id'],
        $data['title'],
        $data['description'],
        $data['lat'],
        $data['lng'],
        $data['severity'],
        $user['id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Alert submitted for verification']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create alert']);
}