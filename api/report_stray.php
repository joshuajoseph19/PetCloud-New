<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_type = $_POST['pet_type'] ?? 'Unknown';
$pet_breed = $_POST['pet_breed'] ?? 'Unknown';
$found_location = $_POST['found_location'] ?? '';
$found_date = $_POST['found_date'] ?? date('Y-m-d');
$description = $_POST['description'] ?? '';
$contact_info = $_POST['contact_info'] ?? '';

if (!$found_location) {
    echo json_encode(['success' => false, 'message' => 'Location is required']);
    exit();
}

$image_url = "https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400"; // Default found pet image

try {
    $stmt = $pdo->prepare("INSERT INTO general_found_pets (reporter_id, pet_type, pet_breed, found_location, found_date, description, contact_info, pet_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $pet_type, $pet_breed, $found_location, $found_date, $description, $contact_info, $image_url]);

    echo json_encode(['success' => true, 'message' => 'Found pet listed! Thank you for helping.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>