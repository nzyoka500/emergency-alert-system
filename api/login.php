<?php
// 1. HEADERS MUST BE FIRST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// 2. HANDLE PREFLIGHT (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/config.php';

// 3. DEBUGGING: Log incoming requests to a file
$raw_input = file_get_contents('php://input');
file_put_contents('api_log.txt', "[" . date('Y-m-d H:i:s') . "] Input: " . $raw_input . PHP_EOL, FILE_APPEND);

// 4. GET DATA
$data = json_decode($raw_input, true);
$username = $data['username'] ?? ''; 
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

try {
    $pdo = getPDO();
    // Allow login via email or full_name (matches your DB columns)
    $stmt = $pdo->prepare("SELECT id, full_name, password, role_id, status FROM users WHERE email = ? OR full_name = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Account inactive']);
            exit;
        }

        // Generate Token
        $token = bin2hex(random_bytes(32));
        $update = $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?");
        $update->execute([$token, $user['id']]);

        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'role' => $user['role_id']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}