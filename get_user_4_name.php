<?php
require_once 'db_connect.php';
$name = $pdo->query("SELECT full_name FROM users WHERE id=4")->fetchColumn();
echo "Name of user 4: [" . $name . "]\n";
?>