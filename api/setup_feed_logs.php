<?php
require_once "db.php";
try {
    $conn->query("CREATE TABLE IF NOT EXISTS feed_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(50) NOT NULL,
        `portion` INT NOT NULL,
        fed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table verified/created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>