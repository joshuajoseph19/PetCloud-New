<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = isset($data['user_id']) ? $data['user_id'] : '';
$listing_id = isset($data['listing_id']) ? $data['listing_id'] : null;
$pet_name = isset($data['pet_name']) ? $data['pet_name'] : '';
$pet_category = isset($data['pet_category']) ? $data['pet_category'] : '';
$full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$reason = isset($data['reason']) ? trim($data['reason']) : '';
$living = isset($data['living_situation']) ? $data['living_situation'] : '';
$other_pets = !empty($data['other_pets']) ? 1 : 0;

if (empty($user_id) || empty($full_name) || empty($email) || empty($phone) || empty($reason) || empty($living)) {
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
    exit;
}

try {
    $sql = "INSERT INTO adoption_applications (user_id, listing_id, pet_name, pet_category, applicant_name, applicant_email, applicant_phone, reason_for_adoption, living_situation, has_other_pets) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$user_id, $listing_id, $pet_name, $pet_category, $full_name, $email, $phone, $reason, $living, $other_pets])) {
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit application.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
