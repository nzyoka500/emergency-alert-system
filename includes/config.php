<?php
/**
 * Database configuration and helper for the Responda application.
 * Update the constants below to match your environment.
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Database connection settings - change as needed
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'emergency_alert_system');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Return a configured PDO instance.
 * Usage: $pdo = getPDO();
 *
 * @return PDO
 */
function getPDO()
{
	static $pdo = null;

	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	try {
		$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
		return $pdo;
	} catch (PDOException $e) {
		error_log('Database connection failed: ' . $e->getMessage());
		// For security, don't expose DB errors to users. You can show a generic message or handle as needed.
		die('Database connection failed.');
	}
}

?>
