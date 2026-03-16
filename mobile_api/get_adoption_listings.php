<?php
/**
 * API Endpoint: Get Adoption Listings with Filters (Mobile Wrapper)
 */

// Disable display errors to prevent HTML appending to JSON output
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../db_connect.php';

    // Check for PDO
    if (!isset($pdo)) {
        throw new Exception("Database connection not available.");
    }

    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 12;
    $offset = ($page - 1) * $limit;

    // Build WHERE clause dynamically
    $whereClauses = ["prl.status = 'Approved'"];
    $params = [];

    // Pet Type Filter
    if (isset($_GET['pet_type_id']) && !empty($_GET['pet_type_id'])) {
        $whereClauses[] = "prl.pet_type_id = ?";
        $params[] = intval($_GET['pet_type_id']);
    }

    $whereSQL = implode(" AND ", $whereClauses);

    // Count total results
    $countQuery = "SELECT COUNT(*) as total
                   FROM pet_rehoming_listings prl
                   WHERE $whereSQL";

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Get listings
    $query = "SELECT 
                prl.id,
                prl.pet_name,
                prl.age_years,
                prl.age_months,
                prl.gender,
                prl.size,
                prl.weight_kg,
                prl.color,
                prl.is_vaccinated,
                prl.is_neutered,
                prl.temperament,
                prl.adoption_fee,
                prl.location,
                prl.city,
                prl.state,
                prl.primary_image,
                prl.views_count,
                prl.is_featured,
                prl.created_at,
                pt.name AS pet_type,
                b.name AS breed_name
              FROM pet_rehoming_listings prl
              JOIN pet_types pt ON prl.pet_type_id = pt.id
              LEFT JOIN breeds b ON prl.breed_id = b.id
              WHERE $whereSQL
              ORDER BY prl.is_featured DESC, prl.created_at DESC
              LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $listings[] = [
            'id' => (int) $row['id'],
            'pet_name' => $row['pet_name'],
            'age' => [
                'years' => (int) $row['age_years'],
                'months' => (int) $row['age_months'],
                'display' => $row['age_years'] . ' yrs'
            ],
            'gender' => $row['gender'],
            'size' => $row['size'],
            'weight_kg' => (float) $row['weight_kg'],
            'image' => $row['primary_image'],
            'pet_type' => [
                'name' => $row['pet_type']
            ],
            'breed' => [
                'name' => $row['breed_name'] ?? 'Unknown'
            ],
            'posted_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $listings,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
