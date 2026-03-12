<?php

require_once __DIR__.'/../includes/config.php';

$pdo=getPDO();

$id=$_POST['id'];

$stmt=$pdo->prepare("DELETE FROM alerts WHERE id=?");
$stmt->execute([$id]);

echo json_encode([
"success"=>true,
"message"=>"Alert deleted successfully"
]);

?>