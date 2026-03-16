<?php
require_once 'db_connect.php';
$status = $pdo->query("SELECT status FROM adoption_listings WHERE id=2")->fetchColumn();
echo "Status of akku (ID 2): [" . $status . "]\n";
?>