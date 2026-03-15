<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Credentials required']);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, password, role_id FROM users WHERE email = ? OR full_name = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Generate a random 64-char token
        $token = bin2hex(random_bytes(32));
        
        // Save token to DB
        $update = $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?");
        $update->execute([$token, $user['id']]);

        echo json_encode([
            'success' => true,
            'token' => $token,
            'role_id' => $user['role_id'],
            'message' => 'Login successful'
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}