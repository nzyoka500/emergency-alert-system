<?php
// register-process.php - handle new user registration
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

if ($full_name === '' || $email === '' || $password === '' || $password_confirm === '') {
    $_SESSION['error'] = 'Please fill all required fields.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please provide a valid email address.';
    header('Location: register.php');
    exit;
}

if ($password !== $password_confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: register.php');
    exit;
}

// Use DB helper
require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = getPDO();

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'An account with that email already exists.';
        header('Location: register.php');
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Default role for registered users -> Responder (role_id = 2)
    $defaultRole = 2;

    $insert = $pdo->prepare('INSERT INTO users (full_name, email, phone, password, role_id, status) VALUES (:full_name, :email, :phone, :password, :role_id, :status)');
    $insert->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':password' => $hash,
        ':role_id' => $defaultRole,
        ':status' => 'active'
    ]);

    $userId = $pdo->lastInsertId();

    // Auto-login new user
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['role_id'] = $defaultRole;
    $_SESSION['status'] = 'active';
    $_SESSION['logged_in'] = true;

    header('Location: dashboard.php');
    exit;

} catch (Exception $e) {
    error_log('Register error: ' . $e->getMessage());
    $_SESSION['error'] = 'Unable to create account right now. Please try again later.';
    header('Location: register.php');
    exit;
}

?>
