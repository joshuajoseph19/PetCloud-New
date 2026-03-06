<?php
require_once 'db_connect.php';
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(100) AFTER payment_id");
    echo "Column 'payment_method' added successfully (or already exists).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>