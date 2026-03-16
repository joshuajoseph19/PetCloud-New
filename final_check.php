<?php
require_once 'db_connect.php';
$stmt = $pdo->query("DESCRIBE daily_tasks");
$cols = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['Field'];
}
echo "Daily Tasks Columns: " . implode(', ', $cols) . "\n";
?>