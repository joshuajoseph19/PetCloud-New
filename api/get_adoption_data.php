<?php
/**
 * API Endpoint: Get Adoption Data (Pet Types, Categories, Breeds)
 * 
 * Modes:
 * 1. No params: Returns all pet types
 * 2. ?start=true: Returns pet types with categories
 * 3. ?pet_type_id=X: Returns categories and breeds for a specific pet type
 */

ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../db_connect.php';
    $pet_type_slug = $_GET['type'] ?? null; // e.g., 'dog'

    // 1. Fetch Categories & Breeds for a specific pet type
    if ($pet_type_slug) {
        // Get Pet Type ID
        // Try new schema first, fallback to old
        try {
            $stmt = $pdo->prepare("SELECT id FROM pet_types WHERE name = ? OR icon LIKE ?"); // name or slug
            $stmt->execute([ucfirst($pet_type_slug), "%$pet_type_slug%"]);
        } catch (PDOException $e) {
            $stmt = $pdo->prepare("SELECT id FROM adoption_pet_types WHERE slug = ?");
            $stmt->execute([$pet_type_slug]);
        }

        $type = $stmt->fetch();

        if (!$type) {
            echo json_encode(['success' => false, 'error' => 'Invalid pet type']);
            exit;
        }

        $typeId = $type['id'];

        // Fetch Categories
        // Check schema again, assume new schema: breeds table with breed_groups
        // Or old schema: breed_categories

        // Let's try to return data in a structure that matches what the app likely expects from this endpoint if it was built for the "old" system
        // If it expects { category_id, category_name, breeds: [] }

        // Try new schema first
        try {
            $catStmt = $pdo->prepare("SELECT id, name FROM breed_groups WHERE is_active = 1 ORDER BY display_order ASC"); // This is generic groups (Pure, Mixed), not type specific usually in new schema, but check
            $catStmt->execute();
            $categories = $catStmt->fetchAll();

            // If this works, we iterate groups.
            // But wait, the prompt for this file implies it's "old system" compatible? 
            // The user error "Adoption fetch error" might be coming from this file if the app hits it.

            $result = [];
            foreach ($categories as $cat) {
                // Fetch Breeds for this user-selected pet type AND this category
                $breedStmt = $pdo->prepare("SELECT id, name FROM breeds WHERE pet_type_id = ? AND breed_group_id = ? AND is_active = 1 ORDER BY name ASC");
                $breedStmt->execute([$typeId, $cat['id']]);
                $breeds = $breedStmt->fetchAll();

                if (count($breeds) > 0) {
                    $result[] = [
                        'category_id' => $cat['id'],
                        'category_name' => $cat['name'],
                        'breeds' => $breeds
                    ];
                }
            }

            // If we got results, output them. If empty, maybe fall back to old schema?
            if (empty($result))
                throw new Exception("No breeds found in new schema");
            echo json_encode(['success' => true, 'data' => $result]);

        } catch (Exception $e) {
            // Fallback to old schema
            $catStmt = $pdo->prepare("SELECT id, name FROM breed_categories WHERE pet_type_id = ? ORDER BY display_order ASC");
            $catStmt->execute([$typeId]);
            $categories = $catStmt->fetchAll();

            $result = [];

            foreach ($categories as $cat) {
                // Fetch Breeds for this category
                $breedStmt = $pdo->prepare("SELECT id, name FROM adoption_breeds WHERE category_id = ? AND is_active = 1 ORDER BY name ASC");
                $breedStmt->execute([$cat['id']]);
                $breeds = $breedStmt->fetchAll();

                $result[] = [
                    'category_id' => $cat['id'],
                    'category_name' => $cat['name'],
                    'breeds' => $breeds
                ];
            }
            echo json_encode(['success' => true, 'data' => $result]);
        }
    }
    // 2. Fetch all Pet Types (for initial load if needed)
    else {
        // Try new schema
        try {
            $stmt = $pdo->prepare("SELECT LOWER(name) as slug, name, icon FROM pet_types WHERE is_active = 1 ORDER BY display_order ASC");
            $stmt->execute();
            $types = $stmt->fetchAll();
        } catch (PDOException $e) {
            $stmt = $pdo->prepare("SELECT slug, name, icon FROM adoption_pet_types WHERE is_active = 1 ORDER BY display_order ASC");
            $stmt->execute();
            $types = $stmt->fetchAll();
        }

        echo json_encode(['success' => true, 'data' => $types]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>