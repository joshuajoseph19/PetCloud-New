<?php
require 'c:\xampp\htdocs\PetCloud\db_connect.php';
$stmt = $pdo->query("SELECT id, user_id, total_amount, status FROM orders ORDER BY id DESC LIMIT 5");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($orders, JSON_PRETTY_PRINT);
?>