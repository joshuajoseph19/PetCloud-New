<?php
/**
 * API Endpoint: Get Services
 * Filters by Category and optionally by Pet Type
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
// We will implement pet_type filtering logic later once the mapping table is fully populated
// $pet_type_id = isset($_GET['pet_type_id']) ? intval($_GET['pet_type_id']) : 0;

try {
    if ($category_id > 0) {
        $stmt = $pdo->prepare("
            SELECT id, name, description, default_duration_minutes, is_medical, is_home_service_supported 
            FROM services 
            WHERE category_id = ? AND is_active = 1 
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute([$category_id]);
    } else {
        // Fallback: Get all
        $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 LIMIT 100");
        $stmt->execute();
    }

    $services = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($services),
        'data' => $services
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>