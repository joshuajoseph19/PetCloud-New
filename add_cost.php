<?php
require_once 'db_connect.php';
try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN cost DECIMAL(10,2) AFTER title");
    echo "Added cost column.\n";
} catch (PDOException $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}
?>