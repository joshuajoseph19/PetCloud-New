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
$alert_id = $_POST['alert_id'] ?? null;
$found_location = $_POST['found_location'] ?? '';
$found_date = $_POST['found_date'] ?? date('Y-m-d');
$notes = $_POST['notes'] ?? '';
$contact_info = $_POST['contact_info'] ?? '';

if (!$alert_id || !$found_location) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO found_pet_reports (alert_id, user_id, found_location, found_date, notes, contact_info) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$alert_id, $user_id, $found_location, $found_date, $notes, $contact_info]);

    echo json_encode(['success' => true, 'message' => 'Found pet report submitted! The owner will be notified.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>