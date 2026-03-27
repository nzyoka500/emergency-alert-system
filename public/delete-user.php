<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

// Admin Only
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]); 
    exit;
}

$pdo = getPDO();
// The 'id' key comes from the JS FormData.append('id', id)
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID missing"]); 
    exit;
}

try {
    // Prevent self-deletion
    // Note: $_SESSION['user_id'] is compared against the Users_id passed in
    if ($id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "message" => "You cannot delete your own account."]); 
        exit;
    }

    // UPDATED: Table 'Users' and Column 'Users_id'
    $stmt = $pdo->prepare("DELETE FROM Users WHERE Users_id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "User account has been deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found or already deleted."]);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error occurred during deletion."]);
}
?>