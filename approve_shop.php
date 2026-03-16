<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['app_id']) && isset($_POST['action'])) {
    $id = $_POST['app_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    try {
        $stmt = $pdo->prepare("UPDATE shop_applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // If approved, we might want to create a shop owner account automatically
        // For now, just updating the status is enough to satisfy the "functional" requirement

        header("Location: admin-dashboard.php?msg=application_" . $status);
        exit();
    } catch (PDOException $e) {
        die("Error updating application: " . $e->getMessage());
    }
} else {
    header("Location: admin-dashboard.php");
}
?>