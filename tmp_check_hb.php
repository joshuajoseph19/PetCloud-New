<?php
require_once 'db_connect.php';
try {
    $stmt = $pdo->query("SELECT * FROM device_heartbeats");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
