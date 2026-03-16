<?php
/**
 * Email Configuration for PetCloud
 * Used by PHPMailer to send password reset links and notifications.
 */

// SMTP Server Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'joshuajoseph0310@gmail.com');
define('SMTP_PASSWORD', 'kilducuhfsqkrrba'); // App Password

// Sender Information
define('SMTP_FROM_EMAIL', 'joshuajoseph0310@gmail.com');
define('SMTP_FROM_NAME', 'PetCloud Support');

/**
 * IMPORTANT: To use Gmail SMTP:
 * 1. Enable 2-Step Verification on the Gmail account.
 * 2. Generate an "App Password" (Settings > Security > App Passwords).
 * 3. Use that 16-character password in SMTP_PASSWORD.
 */
?>
