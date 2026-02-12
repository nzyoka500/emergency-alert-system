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

	// Allow users to login using email, full_name, or phone
	// Use positional placeholders to avoid driver issues with repeated named parameters
	$stmt = $pdo->prepare('SELECT id, full_name, email, phone, password, role_id, status FROM users WHERE email = ? OR full_name = ? OR phone = ? LIMIT 1');
	$stmt->execute([$username, $username, $username]);
	$user = $stmt->fetch();

	if (!$user) {
		// No user found for the provided identifier
		$isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
		if ($isEmail) {
			$_SESSION['error'] = 'No account found with that email address.';
		} else {
			$_SESSION['error'] = 'No account found with that username or phone number.';
		}
		// Log for debugging (do not expose to user)
		error_log(sprintf('Login attempt: identifier="%s" - user not found', $username));
		header('Location: index.php');
		exit;
	}

	// If we have a user, enforce active status first
	if (isset($user['status']) && $user['status'] !== 'active') {
		$_SESSION['error'] = 'Account is not active. Please contact administrator.';
		header('Location: index.php');
		exit;
	}

	// At this point we have a user
	// For debugging: temporarily store whether stored password looks hashed
	$stored = $user['password'] ?? '';
	$looks_hashed = (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || strpos($stored, '$argon2') === 0);

	// Continue to password checks

	if ($user) {
		// Assume passwords are hashed with password_hash(). Use password_verify().
		if (!empty($user['password']) && password_verify($password, $user['password'])) {
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $user['full_name'] ?? $user['email'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['role_id'] = $user['role_id'];
			$_SESSION['status'] = $user['status'];
			$_SESSION['logged_in'] = true;
			header('Location: dashboard.php');
			exit;
		}

		// Fallback (only if your DB stores plain text passwords) - NOT recommended.
		if ($password === $user['password']) {
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $user['full_name'] ?? $user['email'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['role_id'] = $user['role_id'];
			$_SESSION['status'] = $user['status'];
			$_SESSION['logged_in'] = true;
			header('Location: dashboard.php');
			exit;
		}
	}

	// If we reach here, password verification failed
	// Log debug info about the failed auth attempt (mask sensitive parts)
	$stored = $user['password'] ?? '';
	$stored_preview = substr($stored, 0, 10); // preview only
	$pv = password_verify($password, $stored) ? 'true' : 'false';
	error_log(sprintf('Login failed: identifier="%s", user_id=%s, password_verify=%s, stored_preview=%s', $username, $user['id'], $pv, $stored_preview));

	// Friendly message for users
	$_SESSION['error'] = 'Incorrect password. Please try again.';
	header('Location: index.php');
	exit;

} catch (Exception $e) {
	// Log full error for debugging/support
	$ref = strtoupper(substr(sha1(uniqid('', true)), 0, 8));
	error_log("Login error [ref:{$ref}]: " . $e->getMessage());

	// Show a credential-focused message so users know what to check
	$_SESSION['error'] = 'Unable to verify credentials right now. Please check your username/email and password and try again.';

	header('Location: index.php');
	exit;
}

?>
