<?php
// login-process.php
// Handles login form submission, authenticates user, and redirects to dashboard.php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: index.php');
	exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
	$_SESSION['error'] = 'Please provide both username and password.';
	header('Location: index.php');
	exit;
}

// Use centralized database config
require_once __DIR__ . '/../includes/config.php';

try {
	$pdo = getPDO();

	// Allow users to login using email or full name
	$stmt = $pdo->prepare('SELECT id, full_name, email, password FROM users WHERE email = :identifier OR full_name = :identifier LIMIT 1');
	$stmt->execute([':identifier' => $username]);
	$user = $stmt->fetch();

	if ($user) {
		// Assume passwords are hashed with password_hash(). Use password_verify().
		if (password_verify($password, $user['password'])) {
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			// prefer full_name, fall back to email
			$_SESSION['username'] = $user['full_name'] ?? $user['email'];
			$_SESSION['logged_in'] = true;
			header('Location: dashboard.php');
			exit;
		}

		// Fallback (only if your DB stores plain text passwords) - NOT recommended.
		if ($password === $user['password']) {
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $user['full_name'] ?? $user['email'];
			$_SESSION['logged_in'] = true;
			header('Location: dashboard.php');
			exit;
		}
	}

	// Authentication failed
	$_SESSION['error'] = 'Invalid username or password.';
	header('Location: index.php');
	exit;

} catch (PDOException $e) {
	error_log('Login error: ' . $e->getMessage());
	$_SESSION['error'] = 'An internal error occurred. Please try again later.';
	header('Location: index.php');
	exit;
}

?>
