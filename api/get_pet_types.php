<?php
/**
 * API Endpoint: Get All Active Pet Types
 * Returns list of pet types for dropdown selection
 */

ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../db_connect.php';
    // Try to query 'pet_types' first (New Scheme), fall back to 'adoption_pet_types' (Old Schema) or return both if needed
    // The previous code was using 'adoption_pet_types'.
    // However, get_adoption_listings.php uses 'pet_types'.
    // We should probably standardise. For now, let's try 'pet_types' as it matches the listings.

    $query = "SELECT id, name, icon FROM pet_types WHERE is_active = 1 ORDER BY display_order, name";

    try {
        $stmt = $pdo->query($query);
        $petTypes = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback to old table if new one doesn't exist or error (though it should exist based on previous steps)
        $query = "SELECT id, name, icon FROM adoption_pet_types WHERE is_active = 1 ORDER BY display_order, name";
        $stmt = $pdo->query($query);
        $petTypes = $stmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'data' => $petTypes,
        'count' => count($petTypes)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>