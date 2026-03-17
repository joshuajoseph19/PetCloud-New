<?php
require_once 'db_connect.php';

try {
    $pdo->exec("ALTER TABLE pet_rehoming_listings ADD COLUMN pet_type_id INT NULL");
    echo "Added pet_type_id to pet_rehoming_listings<br>";
} catch (Exception $e) { echo "pet_type_id error/exists: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE pet_rehoming_listings ADD COLUMN weight_kg DECIMAL(5,2) NULL");
    echo "Added weight_kg to pet_rehoming_listings<br>";
} catch (Exception $e) { echo "weight_kg error/exists: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE pet_rehoming_listings ADD COLUMN pet_description TEXT NULL");
    echo "Added pet_description to pet_rehoming_listings<br>";
} catch (Exception $e) { echo "pet_description error/exists: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_type VARCHAR(50) NULL");
    echo "Added pet_type to user_pets<br>";
} catch (Exception $e) { echo "pet_type error/exists: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_weight VARCHAR(50) NULL");
    echo "Added pet_weight to user_pets<br>";
} catch (Exception $e) { echo "pet_weight error/exists: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_description TEXT NULL");
    echo "Added pet_description to user_pets<br>";
} catch (Exception $e) { echo "pet_description error/exists: " . $e->getMessage() . "<br>"; }

echo "Database update script finished. You can now use the app!";
?>
