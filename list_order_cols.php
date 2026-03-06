<?php
require 'c:\xampp\htdocs\PetCloud\db_connect.php';
$stmt = $pdo->query("SHOW COLUMNS FROM orders");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}
?>