<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

// Admin Only
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]); exit;
}

$pdo = getPDO();
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID missing"]); exit;
}

try {
    // Prevent self-deletion
    if ($id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "message" => "You cannot delete your own account."]); exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["success" => true, "message" => "User deleted successfully."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error."]);
}