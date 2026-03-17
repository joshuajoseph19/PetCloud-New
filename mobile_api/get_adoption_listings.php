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

    // Count total results - Union logic
    $countQuery = "SELECT SUM(total) as total FROM (
        SELECT COUNT(*) as total FROM pet_rehoming_listings prl WHERE prl.status = 'Approved' " . (isset($_GET['pet_type_id']) ? " AND prl.pet_type_id = ?" : "") . "
        UNION ALL
        SELECT COUNT(*) as total FROM adoption_listings al WHERE al.status IN ('active', 'Approved') " . (isset($_GET['pet_type_id']) ? " AND (
            CASE 
                WHEN ? = 1 THEN al.pet_type = 'dog'
                WHEN ? = 2 THEN al.pet_type = 'cat'
                WHEN ? = 3 THEN al.pet_type = 'bird'
                WHEN ? = 4 THEN al.pet_type = 'rabbit'
                ELSE 0
            END
        )" : "") . "
    ) as combined_total";

    $countStmt = $pdo->prepare($countQuery);
    
    $paramsCount = [];
    if (isset($_GET['pet_type_id'])) {
        $typeId = intval($_GET['pet_type_id']);
        $paramsCount[] = $typeId; // v2
        $paramsCount[] = $typeId; // v1 CASE 1
        $paramsCount[] = $typeId; // v1 CASE 2
        $paramsCount[] = $typeId; // v1 CASE 3
        $paramsCount[] = $typeId; // v1 CASE 4
    }

    $countStmt->execute($paramsCount);
    $totalRecords = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Get listings - Direct UNION to fetch from both tables
    // We Map adoption_listings (legacy) to match the mobile app's expected structure
    $query = "(SELECT 
                prl.id as id,
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
                prl.primary_image as image,
                prl.views_count,
                prl.is_featured,
                prl.created_at,
                pt.name AS pet_type_name,
                b.name AS breed_name,
                'v2' as source_version
              FROM pet_rehoming_listings prl
              JOIN pet_types pt ON prl.pet_type_id = pt.id
              LEFT JOIN breeds b ON prl.breed_id = b.id
              WHERE prl.status = 'Approved' " . (isset($_GET['pet_type_id']) ? " AND prl.pet_type_id = ?" : "") . ")
              UNION ALL
              (SELECT 
                al.id as id,
                al.pet_name,
                CAST(SUBSTRING_INDEX(al.age, ' ', 1) AS UNSIGNED) as age_years,
                0 as age_months,
                al.gender,
                'Medium' as size,
                0.0 as weight_kg,
                'Unknown' as color,
                1 as is_vaccinated,
                0 as is_neutered,
                al.description as temperament,
                0.0 as adoption_fee,
                'Local' as location,
                'Unknown' as city,
                'Unknown' as state,
                al.image_url as image,
                0 as views_count,
                0 as is_featured,
                al.created_at,
                al.pet_type as pet_type_name,
                al.breed as breed_name,
                'v1' as source_version
              FROM adoption_listings al
              WHERE al.status IN ('active', 'Approved') " . (isset($_GET['pet_type_id']) ? " AND (
                CASE 
                    WHEN ? = 1 THEN al.pet_type = 'dog'
                    WHEN ? = 2 THEN al.pet_type = 'cat'
                    WHEN ? = 3 THEN al.pet_type = 'bird'
                    WHEN ? = 4 THEN al.pet_type = 'rabbit'
                    ELSE 0
                END
              )" : "") . ")
              ORDER BY is_featured DESC, created_at DESC
              LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($query);
    
    // Prepare bind parameters
    $bindParams = [];
    if (isset($_GET['pet_type_id'])) {
        $typeId = intval($_GET['pet_type_id']);
        $bindParams[] = $typeId; // For v2
        $bindParams[] = $typeId; // For v1 CASE 1
        $bindParams[] = $typeId; // For v1 CASE 2
        $bindParams[] = $typeId; // For v1 CASE 3
        $bindParams[] = $typeId; // For v1 CASE 4
    }

    $stmt->execute($bindParams);
    $listings = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format image URL
        $imageUrl = $row['image'] ?: 'images/placeholder-pet.jpg';
        if (!preg_match('/^http/', $imageUrl)) {
            $imageUrl = 'https://petcloud-new.onrender.com/' . ltrim($imageUrl, '/');
        }

        // Format age display
        $ageDisplay = $row['age_years'] . ' yrs';
        if ($row['age_years'] == 0 && $row['age_months'] > 0) {
            $ageDisplay = $row['age_months'] . ' mos';
        }

        $listings[] = [
            'id' => (int) $row['id'],
            'pet_name' => $row['pet_name'],
            'age' => [
                'years' => (int) $row['age_years'],
                'months' => (int) $row['age_months'],
                'display' => $ageDisplay
            ],
            'gender' => $row['gender'] ?: 'Unknown',
            'size' => $row['size'] ?: 'Medium',
            'weight_kg' => (float) $row['weight_kg'],
            'image' => $imageUrl,
            'pet_type' => [
                'name' => ucfirst($row['pet_type_name'])
            ],
            'breed' => [
                'name' => $row['breed_name'] ?: 'Unknown'
            ],
            'posted_at' => $row['created_at'],
            'source' => $row['source_version']
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
