<?php
require_once 'db_connect.php';
$stmt = $pdo->query("DESCRIBE health_records");
$cols = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['Field'];
}
echo "Health Records Columns: " . implode(', ', $cols) . "\n";
?>