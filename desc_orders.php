<?php
require 'c:\xampp\htdocs\PetCloud\db_connect.php';
$stmt = $pdo->query("DESC orders");
$desc = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($desc, JSON_PRETTY_PRINT);
?>