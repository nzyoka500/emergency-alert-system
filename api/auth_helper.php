<?php
require_once __DIR__ . '/../includes/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight "OPTIONS" requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

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