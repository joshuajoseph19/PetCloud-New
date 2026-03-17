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

$symptom = isset($data['symptom']) ? trim($data['symptom']) : '';

if (empty($symptom)) {
    echo json_encode(['success' => false, 'error' => 'Please provide a symptom description.']);
    exit;
}

// For this mobile app implementation we are mocking the AI response as a placeholder, 
// as we don't have direct access to the `chatbot_backend.py` from PHP without curl/requests logic 
// that might be brittle. We can respond with a general prompt.
// Ideal future implementation: Call google.genai API directly from PHP or proxy via Python.

function getMockResponse($symptom) {
    // A simple mock since we couldn't find the exact integration logic in the website
    // You could replace this section with a cURL request to a Python backend, 
    // or directly to the Gemini REST API if you have an API key!
    return "I've analyzed the symptom: '" . htmlspecialchars($symptom) . "'. Based on common patterns, I recommend checking for a fever and ensuring your pet is hydrated. If the condition persists for more than 24 hours or if your pet seems lethargic or in pain, please consult a veterinarian immediately.";
}

$response = getMockResponse($symptom);

echo json_encode(['success' => true, 'response' => $response]);
?>
