<?php

require_once __DIR__ . '/../includes/config.php';

session_start();

if ($_SESSION['role_id'] != 1) {
    header("Location: users.php");
    exit;
}

$pdo = getPDO();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: users.php");
exit;