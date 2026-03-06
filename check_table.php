<?php
require_once 'db_connect.php';
$stmt = $pdo->query("DESCRIBE user_pets");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>