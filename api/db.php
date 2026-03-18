<?php
$host = getenv('DB_HOST') ?: 'mysql-2f4ee15-mca-9b42.f.aivencloud.com';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASS') ?: ''; // Add DB_PASS in Render dashboard env vars
$db   = getenv('DB_NAME') ?: 'defaultdb';
$port = getenv('DB_PORT') ?: '17032';

$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// Aiven requires SSL for secure connection
if (!$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connect Error: " . $conn->connect_error);
}

if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');
$conn->query("SET time_zone = '+05:30'");
?>