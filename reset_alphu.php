<?php
require_once 'db_connect.php';
$email = 'alphu@gmail.com';
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$hash, $email]);
echo "Password for $email explicitly reset to '$password'.\n";
?>