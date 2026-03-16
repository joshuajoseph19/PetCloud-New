<?php
/**
 * PetCloud Configuration File
 * Handle API keys and environment settings (via Environment Variables)
 */

// Deployment Environment - set PAYMENT_MODE env var to 'live' for production
define('PAYMENT_MODE', getenv('PAYMENT_MODE') ?: 'test');

// Razorpay Credentials (set these as Environment Variables in Render)
if (PAYMENT_MODE === 'live') {
    define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
    define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
} else {
    define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
    define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
}
?>