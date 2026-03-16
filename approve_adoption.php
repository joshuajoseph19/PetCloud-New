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
        $stmt = $pdo->prepare("UPDATE adoption_applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        header("Location: admin-dashboard.php?msg=adoption_" . $status);
        exit();
    } catch (PDOException $e) {
        die("Error updating adoption: " . $e->getMessage());
    }
} else {
    header("Location: admin-dashboard.php");
}
?>