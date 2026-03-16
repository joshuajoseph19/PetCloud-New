<?php
require 'db_connect.php';
require 'email_config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';

$msg = "";
$status = "pending";
$resetLink = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store in DB
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$email, $token, $expires]);

        // Create Reset Link
        $resetLink = "http://localhost/PetCloud/reset-password.php?token=" . $token;

        // Send Email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            //Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'PetCloud Password Reset Request';
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>We received a request to reset your PetCloud password.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, you can safely ignore this email.</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>PetCloud - Your Pet's Best Friend</p>
            ";
            $mail->AltBody = "Reset your password here: $resetLink (Link expires in 1 hour)";

            $mail->send();
            $status = "success";
            $msg = "Password reset email has been sent! Please check your inbox (and spam folder).";
        } catch (Exception $e) {
            $status = "error";
            $msg = "Failed to send email. Error: {$mail->ErrorInfo}<br><br><b>Troubleshooting:</b><br>1. Check your Gmail credentials in <code>email_config.php</code><br>2. Make sure you're using an App Password (not your regular Gmail password)<br><br><b>Debug Link:</b> <a href='$resetLink'>Click to Reset</a>";
        }

    } else {
        $status = "error";
        $msg = "Email not found in our records.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Status - PetCloud</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <div class="auth-container" style="max-width: 600px; min-height: auto; padding: 2rem; border-radius: 1rem;">
        <div style="text-align: center; width: 100%;">
            <div class="logo-icon-bg"
                style="margin: 0 auto 1.5rem; background: <?php echo $status === 'success' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $status === 'success' ? '#10b981' : '#ef4444'; ?>;">
                <i
                    class="fa-solid fa-<?php echo $status === 'success' ? 'envelope-circle-check' : 'circle-exclamation'; ?>"></i>
            </div>

            <h2 style="margin-bottom: 1rem;"><?php echo $status === 'success' ? 'Check Your Email' : 'Email Status'; ?>
            </h2>

            <div
                style="background: <?php echo $status === 'success' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $status === 'success' ? '#065f46' : '#991b1b'; ?>; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: left;">
                <?php echo $msg ? $msg : "Processing..."; ?>
            </div>

            <a href="index.php" class="btn btn-primary"
                style="width: 100%; display: inline-block; text-decoration: none;">Back to Login</a>
        </div>
    </div>
</body>

</html>