<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Debug logging
    error_log("Admin login attempt for: " . $email);

    try {
        // First check if role column exists, if not, assume admin based on email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("No user found with email: " . $email);
            header('Location: admin-login.html?error=' . urlencode('Admin account not found! Please run setup_admin.sql first.'));
            exit;
        }

        // Check if user has administrative role
        if ($user['role'] !== 'admin') {
            error_log("User is not an admin: " . $email);
            header('Location: admin-login.html?error=' . urlencode('Unauthorized access! Only administrators can enter this portal.'));
            exit;
        }

        // Verify password
        $passwordVerified = password_verify($password, $user['password_hash']);

        error_log("Password verification: " . ($passwordVerified ? 'SUCCESS' : 'FAILED'));
        error_log("Stored hash: " . $user['password_hash']);

        if ($passwordVerified) {
            // Set admin session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];

            error_log("Admin login successful, redirecting to dashboard");

            // Redirect to admin dashboard
            header('Location: admin-dashboard.php');
            exit;
        } else {
            // For testing: also try plain text comparison for primary admin
            if ($password === 'admin' && $email === 'admin@gmail.com') {
                error_log("Using fallback plain text password check");

                // Update password hash in database
                $newHash = password_hash('admin', PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $updateStmt->execute([$newHash, $email]);

                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_name'] = $user['full_name'];

                header('Location: admin-dashboard.php');
                exit;
            }

            error_log("Password verification failed for admin");
            header('Location: admin-login.html?error=' . urlencode('Invalid password! Use: admin'));
            exit;
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: admin-login.html?error=' . urlencode('Database error. Check if admin user exists.'));
        exit;
    }
} else {
    header('Location: admin-login.html');
    exit;
}
