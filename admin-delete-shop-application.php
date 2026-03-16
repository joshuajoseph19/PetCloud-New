<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM shop_applications WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin-shop-approvals.php?msg=Application deleted successfully");
    } catch (Exception $e) {
        header("Location: admin-shop-approvals.php?error=Error deleting application: " . urlencode($e->getMessage()));
    }
} else {
    header("Location: admin-shop-approvals.php");
}
exit;
?>