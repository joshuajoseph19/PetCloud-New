<?php
/**
 * API Endpoint: Get Service Categories
 * Returns the list of 8 main categories (Medical, Grooming, etc.)
 */

header('Content-Type: application/json');
require_once '../db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $categories
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>