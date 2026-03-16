<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../cloudinary_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Missing user_id']);
    exit();
}

try {
    // Validate required fields
    $requiredFields = ['pet_type_id', 'pet_name', 'gender', 'reason_for_rehoming', 'location', 'city', 'state'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Required field missing: $field");
        }
    }

    $petTypeId = intval($_POST['pet_type_id']);
    $breedId = isset($_POST['breed_id']) && !empty($_POST['breed_id']) ? intval($_POST['breed_id']) : null;
    $petName = trim($_POST['pet_name']);
    $ageYears = isset($_POST['age_years']) && $_POST['age_years'] !== '' ? intval($_POST['age_years']) : null;
    $ageMonths = isset($_POST['age_months']) && $_POST['age_months'] !== '' ? intval($_POST['age_months']) : null;
    $gender = trim($_POST['gender']);
    $size = isset($_POST['size']) && !empty($_POST['size']) ? trim($_POST['size']) : null;
    $weightKg = isset($_POST['weight_kg']) && $_POST['weight_kg'] !== '' ? floatval($_POST['weight_kg']) : null;
    $color = isset($_POST['color']) && !empty($_POST['color']) ? trim($_POST['color']) : null;

    $isVaccinated = isset($_POST['is_vaccinated']) && $_POST['is_vaccinated'] == '1' ? 1 : 0;
    $isNeutered = isset($_POST['is_neutered']) && $_POST['is_neutered'] == '1' ? 1 : 0;
    $healthStatus = isset($_POST['health_status']) ? trim($_POST['health_status']) : null;
    $temperament = isset($_POST['temperament']) ? trim($_POST['temperament']) : null;
    $specialNeeds = isset($_POST['special_needs']) ? trim($_POST['special_needs']) : null;

    $reasonForRehoming = trim($_POST['reason_for_rehoming']);
    $adoptionFee = isset($_POST['adoption_fee']) && $_POST['adoption_fee'] !== '' ? floatval($_POST['adoption_fee']) : 0.00;
    $location = trim($_POST['location']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : null;

    $contactPhone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : null;
    $contactEmail = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : null;

    // Validate gender
    $validGenders = ['Male', 'Female', 'Unknown'];
    if (!in_array($gender, $validGenders)) {
        throw new Exception("Invalid gender value");
    }

    // Validate size if provided
    if ($size !== null) {
        $validSizes = ['Small', 'Medium', 'Large', 'Extra Large'];
        if (!in_array($size, $validSizes)) {
            throw new Exception("Invalid size value");
        }
    }

    $primaryImage = null;
    // Handle image upload via Cloudinary
    if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] === UPLOAD_ERR_OK) {
        $cloudUrl = uploadToCloudinary($_FILES['primary_image']['tmp_name'], 'petcloud/rehoming');
        if ($cloudUrl) {
            $primaryImage = $cloudUrl;
        }
    }

    $query = "INSERT INTO pet_rehoming_listings 
              (user_id, pet_type_id, breed_id, pet_name, age_years, age_months, gender, size, weight_kg, color,
               is_vaccinated, is_neutered, health_status, temperament, special_needs,
               reason_for_rehoming, adoption_fee, location, city, state, pincode,
               contact_phone, contact_email, primary_image, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $user_id,
        $petTypeId,
        $breedId,
        $petName,
        $ageYears,
        $ageMonths,
        $gender,
        $size,
        $weightKg,
        $color,
        $isVaccinated,
        $isNeutered,
        $healthStatus,
        $temperament,
        $specialNeeds,
        $reasonForRehoming,
        $adoptionFee,
        $location,
        $city,
        $state,
        $pincode,
        $contactPhone,
        $contactEmail,
        $primaryImage
    ]);

    $listingId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Pet rehoming listing submitted successfully',
        'listing_id' => $listingId
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>