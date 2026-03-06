<?php
require 'c:\xampp\htdocs\PetCloud\db_connect.php';
$user_id = 8;
try {
    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at as order_date FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Orders found for user 8: " . count($orders) . "\n";
    print_r($orders);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>