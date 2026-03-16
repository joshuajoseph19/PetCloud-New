<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once 'db_connect.php';

$service = $_GET['service'] ?? '';

if (!$service) {
    echo json_encode([]);
    exit;
}

try {
    // Fetch hospitals that offer this service, along with the price
    $stmt = $pdo->prepare("
        SELECT h.id, h.name, h.address, h.image_url, h.rating, hs.price 
        FROM hospitals h
        JOIN hospital_services hs ON h.id = hs.hospital_id
        WHERE hs.service_name = ?
    ");
    $stmt->execute([$service]);
    $hospitals = $stmt->fetchAll();

    echo json_encode($hospitals);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>