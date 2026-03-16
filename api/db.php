<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "petcloud_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("DB connection failed");
}

date_default_timezone_set('Asia/Kolkata');
$conn->query("SET time_zone = '+05:30'");
?>