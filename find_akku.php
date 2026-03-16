<?php
require_once 'db_connect.php';

$petName = 'akku';

echo "Searching for '$petName' in adoption_listings...\n";
$stmt = $pdo->prepare("SELECT * FROM adoption_listings WHERE pet_name = ?");
$stmt->execute([$petName]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) > 0) {
    echo "Found in adoption_listings:\n";
    print_r($rows);
} else {
    echo "NOT FOUND in adoption_listings.\n";
}

echo "\nSearching for '$petName' in pet_rehoming_listings...\n";
$stmt = $pdo->prepare("SELECT * FROM pet_rehoming_listings WHERE pet_name = ?");
$stmt->execute([$petName]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) > 0) {
    echo "Found in pet_rehoming_listings:\n";
    print_r($rows);
} else {
    echo "NOT FOUND in pet_rehoming_listings.\n";
}
?>