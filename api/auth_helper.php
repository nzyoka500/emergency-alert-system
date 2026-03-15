<?php
require_once __DIR__ . '/../includes/config.php';

function validateApiToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("SELECT id, full_name, role_id FROM users WHERE api_token = ? AND status = 'active'");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            return $user; // Return user data if valid
        }
    }

    // If invalid, send 401 Unauthorized
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid API Token']);
    exit;
}