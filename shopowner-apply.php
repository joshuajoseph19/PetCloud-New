<?php
require_once 'db_connect.php';

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $shop_name = trim($_POST['shop_name'] ?? '');
    $business_reg = trim($_POST['business_reg'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $category = trim($_POST['shop_category'] ?? '');
    $years = trim($_POST['years_in_business'] ?? '');
    $desc = trim($_POST['description'] ?? '');


    // --- Server-Side Validation ---
    if (empty($name) || empty($email) || empty($phone) || empty($pass) || empty($shop_name) || empty($address) || empty($category) || empty($years)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^[+]?[0-9\-\s]{10,15}$/", $phone)) {
        $error = "Invalid phone number. Please enter at least 10 digits.";
    } else {
        // Check if email already applied or exists
        $stmt = $pdo->prepare("SELECT id FROM shop_applications WHERE email = ? AND status = 'pending'");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "You already have a pending application!";
        } else {
            $passHash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO shop_applications (full_name, email, phone, password_hash, shop_name, business_reg, address, shop_category, years_in_business, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            try {
                if ($pdo->prepare($sql)->execute([$name, $email, $phone, $passHash, $shop_name, $business_reg, $address, $category, $years, $desc])) {
                    $success = true;
                } else {
                    $error = "Application failed. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Owner Application - PetCloud</title>
    <!-- Combined Scripts -->
    <script src="js/form-validation.js" defer></script>
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
    <style>
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .application-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 3rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
        }

        .section-title i {
            color: #3b82f6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(59, 130, 246, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="application-container">
        <a href="index.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Login
        </a>

        <?php if ($success): ?>
            <div style="text-align: center; padding: 2rem;">
                <div
                    style="width: 80px; height: 80px; background: #d1fae5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h1>Application Submitted!</h1>
                <p style="color: #6b7280; margin-top: 1rem; line-height: 1.6;">
                    Thank you for applying to be a shop owner. Our admin team will review your application.
                    You will be able to log in once your application is approved.
                </p>
                <a href="index.php" class="btn btn-primary"
                    style="margin-top: 2rem; display: inline-block; text-decoration: none; padding: 0.75rem 2rem; background: #3b82f6; color: white; border-radius: 0.5rem; font-weight: 600;">Back
                    to Login</a>
            </div>
        <?php else: ?>
            <div class="header">
                <div class="header-icon">
                    <i class="fa-solid fa-store"></i>
                </div>
                <h1>Shop Owner Application</h1>
                <p>Register your pet accessories shop on PetCloud</p>
            </div>

            <?php if ($error): ?>
                <div
                    style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-section">
                    <h3 class="section-title"><i class="fa-solid fa-user"></i> Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" placeholder="John Doe" required
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" placeholder="john@example.com" required
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" placeholder="+1 234 567 8900" required
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Login Password *</label>
                            <input type="password" name="password" placeholder="Create password" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fa-solid fa-briefcase"></i> Business Information</h3>
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label>Shop Name *</label>
                            <input type="text" name="shop_name" placeholder="Pet Paradise Store" required
                                value="<?php echo htmlspecialchars($_POST['shop_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group form-group-full">
                            <label>Business Registration Number</label>
                            <input type="text" name="business_reg" placeholder="Optional"
                                value="<?php echo htmlspecialchars($_POST['business_reg'] ?? ''); ?>">
                        </div>
                        <div class="form-group form-group-full">
                            <label>Shop Address *</label>
                            <textarea name="address" placeholder="Enter your shop address"
                                required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Product Category *</label>
                            <select name="shop_category" required>
                                <option value="">Select category</option>
                                <option value="Food & Treats">Pet Food & Treats</option>
                                <option value="Toys & Accessories">Toys & Accessories</option>
                                <option value="Grooming Supplies">Grooming Supplies</option>
                                <option value="Health & Wellness">Health & Wellness</option>
                                <option value="Pet Furniture">Pet Furniture</option>
                                <option value="All Categories">All Categories</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Years in Business *</label>
                            <input type="number" name="years_in_business" placeholder="e.g., 3" min="0" required
                                value="<?php echo htmlspecialchars($_POST['years_in_business'] ?? ''); ?>">
                        </div>
                        <div class="form-group form-group-full">
                            <label>Description of your business</label>
                            <textarea name="description"
                                placeholder="Brief description of your shop and products..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-paper-plane"></i> Submit Application
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>