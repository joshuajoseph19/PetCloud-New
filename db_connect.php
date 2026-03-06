<?php
// Database configuration
$host = 'localhost';
$dbname = 'petcloud_db';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set correct timezone
    date_default_timezone_set('Asia/Kolkata');
    $pdo->exec("SET time_zone = '+05:30'");

} catch (PDOException $e) {
    // If this is an API request (implied by JSON header usually, but here we can just ensure valid JSON if it fails)
    // We strive to return JSON error if possible, but db_connect is included by pages too.
    // For safety in hybrid environment:
    error_log("Database connection failed: " . $e->getMessage());
    // Don't output HTML. Just die with a message that is valid text.
    // Ideally, the API wrapper handles the JSON encoding.
    // Throwing ensures the API try-catch block catches it if inside one.
    throw $e;
}

// Helper function for admin logging
function logAdminActivity($pdo, $adminName, $action, $targetType = null, $targetId = null)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_activity_logs (admin_name, action, target_type, target_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminName, $action, $targetType, $targetId]);
    } catch (PDOException $e) {
        // Silently fail logging, don't break app
        error_log("Failed to log admin activity: " . $e->getMessage());
    }
}