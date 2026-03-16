<?php
/**
 * API Endpoint: Submit Pet Rehoming Listing
 * Handles form submission for pet rehoming
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Disable display errors to prevent HTML appending to JSON output
ini_set('display_errors', 0);
require_once '../db_connect.php';
session_start();

try {
    // Check for PDO
    if (!isset($pdo)) {
        throw new Exception("Database connection not available.");
    }

    // Check if user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        // Maybe allow testing without login if needed? Assuming strict for now
        throw new Exception("User must be logged in to submit a rehoming listing");
    }

    // Validate required fields
    $requiredFields = ['pet_type_id', 'pet_name', 'gender', 'reason_for_rehoming', 'location', 'city', 'state'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Required field missing: $field");
        }
    }

    // Sanitize and validate inputs
    $petTypeId = intval($_POST['pet_type_id']);
    $breedId = isset($_POST['breed_id']) && !empty($_POST['breed_id']) ? intval($_POST['breed_id']) : null;
    $petName = trim($_POST['pet_name']);
    $ageYears = isset($_POST['age_years']) && !empty($_POST['age_years']) ? intval($_POST['age_years']) : null;
    $ageMonths = isset($_POST['age_months']) && !empty($_POST['age_months']) ? intval($_POST['age_months']) : null;
    $gender = trim($_POST['gender']);
    $size = isset($_POST['size']) && !empty($_POST['size']) ? trim($_POST['size']) : null;
    $weightKg = isset($_POST['weight_kg']) && !empty($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : null;
    $color = isset($_POST['color']) && !empty($_POST['color']) ? trim($_POST['color']) : null;

    // Health & Behavior
    $isVaccinated = isset($_POST['is_vaccinated']) ? 1 : 0;
    $isNeutered = isset($_POST['is_neutered']) ? 1 : 0;
    $healthStatus = isset($_POST['health_status']) ? trim($_POST['health_status']) : null;
    $temperament = isset($_POST['temperament']) ? trim($_POST['temperament']) : null;
    $specialNeeds = isset($_POST['special_needs']) ? trim($_POST['special_needs']) : null;

    // Rehoming Details
    $reasonForRehoming = trim($_POST['reason_for_rehoming']);
    $adoptionFee = isset($_POST['adoption_fee']) && !empty($_POST['adoption_fee']) ? floatval($_POST['adoption_fee']) : 0.00;
    $location = trim($_POST['location']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = isset($_POST['pincode']) && !empty($_POST['pincode']) ? trim($_POST['pincode']) : null;

    // Contact
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

    // Handle image upload
    $primaryImage = null;
    if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/rehoming/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['primary_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('pet_') . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $uploadPath)) {
            // Store relative path in DB
            $primaryImage = 'uploads/rehoming/' . $fileName;
        }
    }

    // Insert into database
    $query = "INSERT INTO pet_rehoming_listings 
              (user_id, pet_type_id, breed_id, pet_name, age_years, age_months, gender, size, weight_kg, color,
               is_vaccinated, is_neutered, health_status, temperament, special_needs,
               reason_for_rehoming, adoption_fee, location, city, state, pincode,
               contact_phone, contact_email, primary_image, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $stmt = $pdo->prepare($query);

    $params = [
        $userId,
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
    ];

    $stmt->execute($params);
    $listingId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Pet rehoming listing submitted successfully',
        'listing_id' => $listingId,
        'status' => 'Pending approval'
    ]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$pdo = null;
?>