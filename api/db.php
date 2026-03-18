<?php
$host = getenv('DB_HOST') ?: 'mysql-2f4ee15-mca-9b42.f.aivencloud.com';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASS') ?: ''; 
$db   = getenv('DB_NAME') ?: 'defaultdb';
$port = getenv('DB_PORT') ?: '17032';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');
$conn->query("SET time_zone = '+05:30'");
?>