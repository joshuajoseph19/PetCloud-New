<?php
/**
 * PetCloud Configuration File
 * Handle API keys and environment settings
 */

// Deployment Environment ('test' or 'live')
define('PAYMENT_MODE', 'test');

// Razorpay Credentials
// Get these from: https://dashboard.razorpay.com/app/keys
if (PAYMENT_MODE === 'live') {
    define('RAZORPAY_KEY_ID', 'rzp_live_xxxxxxxxxxxxxx');
    define('RAZORPAY_KEY_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxx');
} else {
    define('RAZORPAY_KEY_ID', 'rzp_test_SA0zw7u9R4lnqU');
    define('RAZORPAY_KEY_SECRET', 'ZjyBszweN22q5Ecn5KelN2qo');
}

// Database Configuration (already handled in db_connect.php, but good for reference)
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'petcloud');
?>