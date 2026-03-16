<?php
/**
 * Keep-Alive Ping Endpoint
 * 
 * Used by an external cron job (e.g., cron-job.org) to prevent
 * Render's free tier from spinning down due to inactivity.
 * 
 * Configure your cron job to call this URL every 10 minutes:
 *   https://your-render-url.onrender.com/ping.php
 */
header('Content-Type: application/json');
echo json_encode([
    'status' => 'alive',
    'time'   => date('Y-m-d H:i:s'),
]);
