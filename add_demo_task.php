<?php
require_once 'db_connect.php';
try {
    // Add a demo task for today
    $pdo->prepare("INSERT INTO daily_tasks (user_id, task_name, task_time, task_date, frequency) VALUES (?, ?, ?, ?, ?)")
        ->execute([2, 'Check fixed health system', '20:00', date('Y-m-d'), 'Daily']);
    echo "Added demo task.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>