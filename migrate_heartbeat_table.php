<?php
/**
 * One-Time Migration: Create device_heartbeats table
 * 
 * Run this ONCE on Render after deploying:
 *   https://your-render-url.onrender.com/migrate_heartbeat_table.php
 * 
 * Safe to run multiple times (uses IF NOT EXISTS).
 */
require_once 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS device_heartbeats (
    device_id  VARCHAR(50) NOT NULL PRIMARY KEY,
    last_seen  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo json_encode([
        "success" => true,
        "message" => "device_heartbeats table created (or already exists). Migration complete!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ]);
}
?>
