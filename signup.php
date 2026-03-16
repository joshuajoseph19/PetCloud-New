<?php
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($pass !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email already registered! Try logging in.";
            } else {
                // Hash password
                $hash = password_hash($pass, PASSWORD_DEFAULT);

                // Insert User (default role is client)
                $sql = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'client')";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$name, $email, $hash])) {
                    // Start session and login immediately
                    session_start();
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['role'] = 'client';

                    // Redirect to Dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Registration failed! Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PetCloud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Validation -->
    <script src="js/form-validation.js" defer></script>
</head>

<body class="auth-page">
    <div class="auth-container">
        <!-- Left Side: Image & Branding -->
        <div class="auth-left">
            <img src="images/signup_pets.png" alt="Dog and Cat Friends">
            <div class="auth-overlay">

                <h1 class="auth-headline">
                    Keep your furry friends <br>
                    <span class="highlight-text">happy and full, no matter where you are.</span>
                </h1>
                <p class="auth-subtext">
                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 8px;"></i> Smart scheduling
                    & portion control<br>
                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 8px; margin-top: 8px;"></i>
                    Real-time feeding notifications<br>
                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 8px; margin-top: 8px;"></i>
                    HD camera integration for monitoring
                </p>
            </div>
        </div>

        <!-- Right Side: Signup Form -->
        <div class="auth-right">
            <div class="brand-logo" style="margin-bottom: 2.5rem; text-align: center;">
                <img src="images/logo.png" alt="PetCloud Logo" style="height: 80px; width: auto; object-fit: contain;">
            </div>

            <div class="auth-header">
                <h2>Create your account</h2>
                <p style="color: var(--text-muted);">Join the Smart Pet Care Revolution</p>
            </div>

            <?php if ($error): ?>
                <div
                    style="background: #fee2e2; color: #ef4444; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="divider" style="margin-top: 0; margin-bottom: 2rem;">Register with email</div>

            <form action="signup.php" method="POST">
                <div class="input-group">
                    <label class="input-label">Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="full_name" class="input-field" placeholder="Enter your full name"
                            style="padding-left: 1rem;" required
                            value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="input-field" placeholder="name@example.com"
                            style="padding-left: 1rem;" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="input-field"
                            placeholder="Create a password" style="padding-left: 1rem;" required>
                        <i class="fa-regular fa-eye" id="toggle-pw1"
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_password" id="confirm-password" class="input-field"
                            placeholder="Confirm your password" style="padding-left: 1rem;" required>
                        <i class="fa-regular fa-eye" id="toggle-pw2"
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                    </div>
                </div>

                <div
                    style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#" style="color: #3b82f6;">Terms of Service</a> and <a
                            href="#" style="color: #3b82f6;">Privacy Policy</a>.</label>
                </div>

                <!-- Google Sign-In Button -->
                <button type="button" id="google-signup-btn" class="btn btn-outline w-full"
                    style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-color: #e5e7eb; color: #111827;">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20">
                    Continue with Google
                </button>

                <div style="text-align: center; margin: 1rem 0; color: #9ca3af; font-size: 0.875rem;">OR</div>

                <button type="submit" class="btn btn-primary w-full shadow-lg" style="background-color: #2563eb;">
                    Create Account <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
            </form>

            <div
                style="margin-top: 1.5rem; padding: 1rem; background: #f0f9ff; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                <p style="color: #1e40af; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9375rem;">
                    <i class="fa-solid fa-store"></i> Are you a Shop Owner?
                </p>
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">
                    Register your pet accessories shop and start selling on PetCloud
                </p>
                <a href="shopowner-apply.html" class="btn btn-outline w-full"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-color: #3b82f6; color: #3b82f6;">
                    <i class="fa-solid fa-briefcase"></i> Apply as Shop Owner
                </a>
            </div>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Already have an account? <a href="index.php" style="color: #2563eb; font-weight: 600;">Log in</a>
            </div>

            <div style="text-align: center; margin-top: 2rem; font-size: 0.75rem; color: #9ca3af;">
                <i class="fa-solid fa-lock"></i> 256-bit SSL Secure Registration
            </div>
        </div>
    </div>

    <script type="module">
        import { signInWithGoogle } from './js/firebase-auth.js';

        // Password toggles
        document.getElementById('toggle-pw1').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('toggle-pw2').addEventListener('click', function () {
            const input = document.getElementById('confirm-password');
            const icon = this;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Google Authentication Logic via Firebase
        window.handleGoogleSignIn = async function () {
            const result = await signInWithGoogle();

            if (result.success) {
                const user = result.user;

                fetch('google-auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: user.email,
                        name: user.displayName,
                        picture: user.photoURL
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.role === 'admin') window.location.href = 'admin-dashboard.php';
                            else if (data.role === 'shop_owner') window.location.href = 'shopowner-dashboard.php';
                            else window.location.href = 'dashboard.php';
                        } else {
                            alert('Backend authentication failed: ' + data.message);
                        }
                    });
            } else {
                alert(result.error || 'Google Sign-In failed.');
            }
        };

        document.getElementById('google-signup-btn').addEventListener('click', window.handleGoogleSignIn);
    </script>
</body>

</html>