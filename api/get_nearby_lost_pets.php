<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's location
$stmt = $pdo->prepare("SELECT location FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_location = $user['location'] ?? '';

// Basic filtering: Assume location is "City, State" or just "City". 
// We'll search for alerts where last_seen_location matches part of the user's location or vice versa.
// For a production app, this would be more robust (coordinates or standardized city names).

$matches = explode(',', $user_location);
$city = trim($matches[0]);

try {
    $sql = "SELECT lpa.*, p.pet_name, p.pet_breed, p.pet_type, p.pet_image, u.full_name as owner_name 
            FROM lost_pet_alerts lpa
            JOIN user_pets p ON lpa.pet_id = p.id
            JOIN users u ON lpa.user_id = u.id
            WHERE lpa.status = 'Active' 
            AND (lpa.last_seen_location LIKE ? OR ? LIKE CONCAT('%', lpa.last_seen_location, '%'))
            ORDER BY lpa.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $search = "%$city%";
    $stmt->execute([$search, $user_location]);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'alerts' => $alerts]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>