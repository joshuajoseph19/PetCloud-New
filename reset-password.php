<?php
require 'db_connect.php';

$msg = "";
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_pass = $_POST['password'];

    // Validate Token and Expiry
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        $email = $stmt->fetchColumn();

        // Hash new password
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);

        // Update User Password
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $update->execute([$hash, $email]);

        // Delete Token to prevent reuse
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        $msg = "Password has been reset! <a href='index.php'>Login Now</a>";
    } else {
        $msg = "Invalid or expired token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PetCloud</title>
    <link rel="stylesheet" href="css/styles.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Validation -->
    <script src="js/form-validation.js" defer></script>
</head>

<body class="auth-page">

    <div class="auth-container" style="max-width: 500px; min-height: auto; width:100%;">
        <div class="auth-right" style="padding: 3rem;">
            <div class="brand-logo" style="justify-content: center; margin-bottom: 1.5rem;">
                <div class="logo-icon-bg"><i class="fa-solid fa-lock"></i></div>
                <span>Reset Password</span>
            </div>

            <?php if ($msg): ?>
                <div style="text-align: center; color: #10b981; font-weight: 600; margin-bottom: 1rem;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="reset-password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="input-group">
                    <label class="input-label">New Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" class="input-field" placeholder="Enter new password"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full shadow-lg">
                    Reset Password
                </button>
            </form>
        </div>
    </div>

</body>

</html>