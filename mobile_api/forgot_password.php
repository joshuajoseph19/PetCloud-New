<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../email_config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-6.9.1/src/Exception.php';
require '../PHPMailer-6.9.1/src/PHPMailer.php';
require '../PHPMailer-6.9.1/src/SMTP.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email'])) {
    echo json_encode(['error' => 'Email is required']);
    exit();
}

$email = $input['email'];

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store in DB (ensure password_resets table exists, or fallback)
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$email, $token, $expires]);

        // Create Reset Link (Pointing to the Render production URL)
        $resetLink = "https://petcloud-new.onrender.com/reset-password.php?token=" . $token;

        // Send Email using PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'PetCloud Mobile - Password Reset';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>We received a request to reset your PetCloud password from the mobile app.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='$resetLink' style='background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Reset email sent successfully']);
    } else {
        echo json_encode(['error' => 'Email not found']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Mailer error: ' . $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
