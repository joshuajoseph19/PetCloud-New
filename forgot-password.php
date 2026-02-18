<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PetCloud</title>

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
            <img src="images/forgot_password_dog.png" alt="Confused cute dog">
            <div class="auth-overlay">
                <div class="trusted-badge">
                    <span class="badge-dot"></span>
                    24/7 Support Available
                </div>
                <h1 class="auth-headline">
                    Don't worry, even the best of us <br>
                    <span class="highlight-text">forget sometimes.</span>
                </h1>
                <p class="auth-subtext">
                    We'll help you reset your password and get you back to caring for your pet in no time.
                </p>
            </div>
        </div>

        <!-- Right Side: Reset Form -->
        <div class="auth-right">
            <div class="brand-logo" style="margin-bottom: 2.5rem; text-align: center;">
                <img src="images/logo.png" alt="PetCloud Logo" style="height: 80px; width: auto; object-fit: contain;">
            </div>

            <div class="auth-header">
                <h2>Forgot Password?</h2>
                <p style="color: var(--text-muted);">Enter your email to receive reset instructions.</p>
            </div>

            <form action="send-password-reset.php" method="POST">
                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" name="email" class="input-field" placeholder="name@example.com" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full shadow-lg">
                    Send Reset Link <i class="fa-solid fa-paper-plane" style="margin-left: 0.5rem;"></i>
                </button>
            </form>

            <div style="text-align: center; margin-top: 2rem; font-size: 0.875rem; color: var(--text-muted);">
                Remembered your password? <a href="index.php" style="color: #10b981; font-weight: 600;">Log in</a>
            </div>
        </div>
    </div>

    <div style="position: absolute; bottom: 1rem; width: 100%; text-align: center;">
        <div class="footer-links">
            <a href="#">Privacy Policy</a> • <a href="#">Terms of Service</a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/main.js"></script>
</body>

</html>