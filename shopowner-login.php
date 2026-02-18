<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'shop_owner') {
    header("Location: shopowner-dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Check users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'shop_owner' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 2. Check if shop is approved
        $stmt = $pdo->prepare("SELECT status FROM shop_applications WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $status = $stmt->fetchColumn();

        if ($status === 'approved') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = 'shop_owner';
            header("Location: shopowner-dashboard.php");
            exit();
        } elseif ($status === 'pending') {
            $error = "Your application is pending approval. Please wait for admin confirmation.";
        } else {
            $error = "Your shop application was not found or has been rejected.";
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shop Owner Login - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Validation -->
    <script src="js/form-validation.js" defer></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-box {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 3rem;
        }

        .shop-badge {
            background: #4f46e5;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        h1 {
            font-family: 'Outfit';
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        p {
            color: #64748b;
            margin-bottom: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        input {
            width: 100%;
            padding: 0.85rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .error-msg {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fee2e2;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .footer-links {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .footer-links a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <span class="shop-badge"><i class="fa-solid fa-store"></i> SHOP OWNER PORTAL</span>
        <h1>Welcome Back</h1>
        <p>Manage your pet business with ease.</p>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="owner@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn">Login to Dashboard</button>
        </form>

        <div class="footer-links">
            Don't have a shop? <a href="shopowner-apply.php">Apply Now</a><br><br>
            <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Marketplace</a>
        </div>
    </div>
</body>

</html>