<?php
/**
 * API Endpoint: Get Breeds by Pet Type
 * Returns breeds filtered by pet type
 * 
 * Usage: get_breeds.php?pet_type_id=1
 */

ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../db_connect.php';
    // Validate parameters
    $petTypeId = isset($_GET['pet_type_id']) ? intval($_GET['pet_type_id']) : null;
    $petTypeSlug = isset($_GET['pet_type']) ? $_GET['pet_type'] : null;

    if (!$petTypeId && !$petTypeSlug) {
        // Return JSON error with 400 Bad Request instead of relying on generic catch
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'pet_type_id or pet_type parameter is required']);
        exit;
    }

    // New Schema uses `breeds` and `breed_groups`. Old schema was `adoption_breeds` and `breed_categories`.
    // We should use the NEW schema (`breeds`) since `get_adoption_listings.php` queries `pet_rehoming_listings` which joins `breeds`.

    // Check if we need to resolve slug to ID
    if (!$petTypeId && $petTypeSlug) {
        $stmt = $pdo->prepare("SELECT id FROM pet_types WHERE name = ? OR icon LIKE ? OR id = ?");
        // Simple heuristic matching
        $stmt->execute([ucfirst($petTypeSlug), "%$petTypeSlug%", $petTypeSlug]);
        $row = $stmt->fetch();
        if ($row) {
            $petTypeId = $row['id'];
        } else {
            // Fallback map
            $map = ['dog' => 1, 'cat' => 2, 'bird' => 3, 'rabbit' => 4];
            $petTypeId = $map[strtolower($petTypeSlug)] ?? 1;
        }
    }

    // Query to get breeds grouped by breed group 
    // New Schema: breeds table has breed_group_id. Join to breed_groups.
    $query = "SELECT 
                bg.id AS group_id,
                bg.name AS group_name,
                bg.display_order AS group_order,
                b.id AS breed_id,
                b.name AS breed_name
              FROM breeds b
              JOIN breed_groups bg ON b.breed_group_id = bg.id 
              WHERE b.pet_type_id = ?
                AND b.is_active = 1
              ORDER BY bg.display_order, b.name";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$petTypeId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback to old schema if new table fails?
        // Old schema: adoption_breeds join breed_categories
        $query = "SELECT 
                bc.id AS group_id,
                bc.name AS group_name,
                bc.display_order AS group_order,
                ab.id AS breed_id,
                ab.name AS breed_name
              FROM adoption_breeds ab
              JOIN breed_categories bc ON ab.category_id = bc.id 
              WHERE bc.pet_type_id = ? AND ab.is_active = 1
              ORDER BY bc.display_order, ab.name";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$petTypeId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Group breeds by breed group
    $groupedBreeds = [];

    foreach ($results as $row) {
        $groupId = $row['group_id'];

        if (!isset($groupedBreeds[$groupId])) {
            $groupedBreeds[$groupId] = [
                'group_id' => (int) $groupId,
                'group_name' => $row['group_name'],
                'group_order' => (int) $row['group_order'],
                'breeds' => []
            ];
        }

        $groupedBreeds[$groupId]['breeds'][] = [
            'id' => (int) $row['breed_id'],
            'name' => $row['breed_name']
        ];
    }

    $breedGroups = array_values($groupedBreeds);
    usort($breedGroups, function ($a, $b) {
        return $a['group_order'] - $b['group_order'];
    });

    $totalBreeds = 0;
    foreach ($breedGroups as $group) {
        $totalBreeds += count($group['breeds']);
    }

    echo json_encode([
        'success' => true,
        'data' => $breedGroups,
        'total_breeds' => $totalBreeds,
        'total_groups' => count($breedGroups)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>