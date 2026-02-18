<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

// Check for signup success
if (isset($_GET['signup']) && $_GET['signup'] == 'success') {
    $success = 'Account created! Please log in.';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    try {
        // Check user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin-dashboard.php");
            } elseif ($user['role'] === 'shop_owner') {
                header("Location: shopowner-dashboard.php"); // or php
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PetCloud</title>

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
            <img src="images/login_dog.png" alt="Happy Golden Retriever">
            <div class="auth-overlay">

                <h1 class="auth-headline">
                    Give your pet <br>
                    the care they deserve.
                </h1>
                <p class="auth-subtext">
                    Join our community of pet lovers. Track health, schedule feedings, and keep your furry friends
                    happy.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="auth-right">
            <div class="brand-logo" style="margin-bottom: 2.5rem; text-align: center;">
                <img src="images/logo.png" alt="PetCloud Logo" style="height: 80px; width: auto; object-fit: contain;">
            </div>

            <div class="auth-header">
                <h2>Welcome back!</h2>
                <p style="color: var(--text-muted);">Please enter your details to sign in.</p>
            </div>

            <?php if ($success): ?>
                <div
                    style="background: #d1fae5; color: #10b981; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;">
                    <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div
                    style="background: #fee2e2; color: #ef4444; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" name="email" class="input-field" placeholder="name@example.com" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <div class="flex justify-between items-center mb-2">
                        <label class="input-label" style="margin-bottom: 0;">Password</label>
                        <a href="forgot-password.html" class="forgot-password">Forgot Password?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="input-field"
                            placeholder="Enter your password" style="padding-left: 2.5rem;" required>
                        <i class="fa-regular fa-eye" id="toggle-pw"
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full shadow-lg" style="margin-top: 1rem;">
                    Sign In <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
            </form>

            <div class="divider">or continue with</div>

            <button type="button" id="google-signin-btn" class="btn btn-outline w-full"
                style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 0.875rem; border-radius: 0.75rem;">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20">
                <span style="font-weight: 600;">Sign in with Google</span>
            </button>

            <div style="text-align: center; margin-top: 2rem; font-size: 0.875rem; color: var(--text-muted);">
                New to PetCloud? <a href="signup.php" style="color: var(--primary-color); font-weight: 600;">Create an
                    account</a>
            </div>

            <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="admin-login.html"
                    style="color: #9ca3af; font-size: 0.8125rem; display: flex; align-items: center; justify-content: center; gap: 0.375rem;">
                    <i class="fa-solid fa-shield-halved"></i> Admin Access
                </a>
            </div>
        </div>
    </div>

    <script type="module">
        import { signInWithGoogle } from './js/firebase-auth.js';

        // Password toggle
        document.getElementById('toggle-pw').addEventListener('click', function () {
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

        document.getElementById('google-signin-btn').addEventListener('click', window.handleGoogleSignIn);
    </script>
</body>

</html>