<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}

// UPDATED: Post keys to match the new Users_ prefix used in the HTML form
$full_name = trim($_POST['Users_full_name'] ?? '');
$email = trim($_POST['Users_email'] ?? '');
$phone = trim($_POST['Users_phone'] ?? '');
$password = $_POST['Users_password'] ?? '';
$role_id = $_POST['Users_Roles_id'] ?? 3;

if ($full_name === '' || $email === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all required fields."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);
    exit;
}

try {

    $pdo = getPDO();

    // UPDATED: Table 'Users' and column 'Users_email'
    $check = $pdo->prepare("SELECT Users_id FROM Users WHERE Users_email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Email already exists."
        ]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // UPDATED: Table 'Users' and prefixed columns
    $stmt = $pdo->prepare("
        INSERT INTO Users
        (Users_full_name, Users_email, Users_phone, Users_password, Users_Roles_id, Users_status)
        VALUES
        (:full_name, :email, :phone, :password, :role_id, 'active')
    ");

    $stmt->execute([
        ':full_name' => $full_name,
        ':email'     => $email,
        ':phone'     => $phone,
        ':password'  => $passwordHash,
        ':role_id'   => $role_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "User created successfully!",
        "redirect" => "users.php"
    ]);

} catch (Exception $e) {

    error_log($e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Failed to create user."
    ]);
}
?>