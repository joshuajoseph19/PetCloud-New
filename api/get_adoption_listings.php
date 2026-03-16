<?php
/**
 * API Endpoint: Get Adoption Listings with Filters
 * Returns pet rehoming listings with optional filtering
 * 
 * Query Parameters:
 * - pet_type_id: Filter by pet type
 * - pet_type: Filter by pet type slug (e.g., 'dog', 'cat')
 * - breed_id: Filter by specific breed
 * - breed_group_id: Filter by breed group (Pure/Mixed/Indie)
 * - city: Filter by city
 * - state: Filter by state
 * - min_age: Minimum age in months
 * - max_age: Maximum age in months
 * - gender: Filter by gender
 * - size: Filter by size
 * - page: Page number (default: 1)
 * - limit: Results per page (default: 12)
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

    // Pet Type Filter (ID has priority, then Slug)
    if (isset($_GET['pet_type_id']) && !empty($_GET['pet_type_id'])) {
        $whereClauses[] = "prl.pet_type_id = ?";
        $params[] = intval($_GET['pet_type_id']);
    } elseif (isset($_GET['pet_type']) && !empty($_GET['pet_type'])) {
        // Look up ID from Slug
        // Common mappings if DB lookup fails (fallback)
        $slug = strtolower(trim($_GET['pet_type']));

        // Try to find ID in DB first
        $typeStmt = $pdo->prepare("SELECT id FROM pet_types WHERE LOWER(name) = ? OR id = ?"); // Assuming name matches slug-ish like 'Dog'
        $typeStmt->execute([$slug, $slug]);
        $typeRow = $typeStmt->fetch(PDO::FETCH_ASSOC);

        if ($typeRow) {
            $whereClauses[] = "prl.pet_type_id = ?";
            $params[] = $typeRow['id'];
        } else {
            // Hardcoded fallback for common cases if DB is empty/mismatched
            $map = ['dog' => 1, 'cat' => 2, 'bird' => 3, 'rabbit' => 4];
            if (isset($map[$slug])) {
                $whereClauses[] = "prl.pet_type_id = ?";
                $params[] = $map[$slug];
            }
        }
    }

    // Breed Filter
    if (isset($_GET['breed_id']) && !empty($_GET['breed_id'])) {
        $whereClauses[] = "prl.breed_id = ?";
        $params[] = intval($_GET['breed_id']);
    }

    // Breed Group Filter
    if (isset($_GET['breed_group_id']) && !empty($_GET['breed_group_id'])) {
        $whereClauses[] = "b.breed_group_id = ?";
        $params[] = intval($_GET['breed_group_id']);
    }

    // City Filter
    if (isset($_GET['city']) && !empty($_GET['city'])) {
        $whereClauses[] = "prl.city LIKE ?";
        $params[] = "%" . $_GET['city'] . "%";
    }

    // State Filter
    if (isset($_GET['state']) && !empty($_GET['state'])) {
        $whereClauses[] = "prl.state LIKE ?";
        $params[] = "%" . $_GET['state'] . "%";
    }

    // Gender Filter
    if (isset($_GET['gender']) && !empty($_GET['gender'])) {
        $whereClauses[] = "prl.gender = ?";
        $params[] = $_GET['gender'];
    }

    // Size Filter
    if (isset($_GET['size']) && !empty($_GET['size'])) {
        $whereClauses[] = "prl.size = ?";
        $params[] = $_GET['size'];
    }

    // Age Filter (in months)
    if (isset($_GET['min_age']) && !empty($_GET['min_age'])) {
        $whereClauses[] = "((prl.age_years * 12) + COALESCE(prl.age_months, 0)) >= ?";
        $params[] = intval($_GET['min_age']);
    }

    if (isset($_GET['max_age']) && !empty($_GET['max_age'])) {
        $whereClauses[] = "((prl.age_years * 12) + COALESCE(prl.age_months, 0)) <= ?";
        $params[] = intval($_GET['max_age']);
    }

    $whereSQL = implode(" AND ", $whereClauses);

    // Count total results
    $countQuery = "SELECT COUNT(*) as total
                   FROM pet_rehoming_listings prl
                   LEFT JOIN breeds b ON prl.breed_id = b.id
                   WHERE $whereSQL";

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $totalResult['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Get listings
    // We inject LIMIT and OFFSET directly as they are sanitized integers
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
                pt.icon AS pet_type_icon,
                b.name AS breed_name,
                bg.name AS breed_group
              FROM pet_rehoming_listings prl
              JOIN pet_types pt ON prl.pet_type_id = pt.id
              LEFT JOIN breeds b ON prl.breed_id = b.id
              LEFT JOIN breed_groups bg ON b.breed_group_id = bg.id
              WHERE $whereSQL
              ORDER BY prl.is_featured DESC, prl.created_at DESC
              LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Calculate total age in months
        $totalMonths = ($row['age_years'] * 12) + ($row['age_months'] ?? 0);

        // Format age display similar to screenshot "2" (years) or "5 months"
        $ageDisplay = '';
        if ($row['age_years'] > 0) {
            $ageDisplay = strval($row['age_years']);
        } elseif ($row['age_months'] > 0) {
            $ageDisplay = $row['age_months'] . ' month' . ($row['age_months'] > 1 ? 's' : '');
        } else {
            $ageDisplay = 'Baby';
        }

        $listings[] = [
            'id' => (int) $row['id'],
            'pet_name' => $row['pet_name'],
            'age' => [
                'years' => (int) $row['age_years'],
                'months' => (int) $row['age_months'],
                'total_months' => $totalMonths,
                'display' => $ageDisplay
            ],
            'gender' => $row['gender'],
            'size' => $row['size'],
            'weight_kg' => $row['weight_kg'] ? (float) $row['weight_kg'] : null,
            'color' => $row['color'],
            'is_vaccinated' => (bool) $row['is_vaccinated'],
            'is_neutered' => (bool) $row['is_neutered'],
            'temperament' => $row['temperament'],
            'adoption_fee' => (float) $row['adoption_fee'],
            'location' => [
                'full' => $row['location'],
                'city' => $row['city'],
                'state' => $row['state']
            ],
            'pet_type' => [
                'name' => $row['pet_type'],
                'icon' => $row['pet_type_icon']
            ],
            'breed' => [
                'name' => $row['breed_name'] ?? 'Unknown',
                'group' => $row['breed_group'] ?? 'Unknown'
            ],
            'image' => (strpos($row['primary_image'], 'http') === 0)
                ? $row['primary_image']
                : 'http://' . $_SERVER['HTTP_HOST'] . '/PetCloud/' . ltrim($row['primary_image'], '/'),
            'views' => (int) $row['views_count'],
            'is_featured' => (bool) $row['is_featured'],
            'posted_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $listings,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ],
        'filters_applied' => array_filter($_GET, function ($key) {
            return in_array($key, ['pet_type_id', 'pet_type', 'breed_id', 'breed_group_id', 'city', 'state', 'gender', 'size', 'min_age', 'max_age']);
        }, ARRAY_FILTER_USE_KEY)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$pdo = null;
?>