<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop_owner') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

// Fetch Shop Details
$stmt = $pdo->prepare("SELECT * FROM shop_applications WHERE email = ? AND status = 'approved' LIMIT 1");
$stmt->execute([$user_email]);
$shop = $stmt->fetch();
$shop_id = $shop['id'];
$shopName = $shop['shop_name'];

$success = "";
$error = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $newShopName = $_POST['shop_name'];
    $category = $_POST['shop_category'];
    $address = $_POST['shop_address'];
    $bank = $_POST['bank_details'];
    $logo = $_POST['shop_logo'];

    $stmt = $pdo->prepare("UPDATE shop_applications SET shop_name = ?, shop_category = ?, shop_address = ?, bank_details = ?, shop_logo = ? WHERE id = ?");
    if ($stmt->execute([$newShopName, $category, $address, $bank, $logo, $shop_id])) {
        $success = "Shop profile updated successfully! 🛠️";
        // Update local vars
        $shopName = $newShopName;
        $shop = array_merge($shop, ['shop_name' => $newShopName, 'shop_category' => $category, 'shop_address' => $address, 'bank_details' => $bank, 'shop_logo' => $logo]);
    }
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $newPass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$newPass, $user_id])) {
        $success = "Password changed successfully!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shop Settings - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
        }

        .main-wrapper {
            margin-left: 280px;
            padding: 2.5rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @media (max-width: 1024px) {
            .main-wrapper {
                margin-left: 0;
                padding: 1.5rem;
            }
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Outfit';
            font-size: 1.75rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-family: 'Outfit';
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <h2>Shop & Account Settings</h2>
            <p style="color: #64748b;">Configure your business profile and security preferences.</p>
        </div>

        <?php if ($success): ?>
            <div
                style="background: #ecfdf5; color: #047857; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Shop Profile -->
            <div class="settings-card">
                <div class="card-title"><i class="fa-solid fa-store" style="color: var(--primary);"></i> Shop Profile
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" name="shop_name" class="form-control"
                            value="<?php echo htmlspecialchars($shop['shop_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Business Category</label>
                        <select name="shop_category" class="form-control">
                            <option value="Food" <?php echo $shop['shop_category'] == 'Food' ? 'selected' : ''; ?>>Food &
                                Nutrition</option>
                            <option value="Toys" <?php echo $shop['shop_category'] == 'Toys' ? 'selected' : ''; ?>>Toys &
                                Play</option>
                            <option value="Services" <?php echo $shop['shop_category'] == 'Services' ? 'selected' : ''; ?>
                                >Grooming & Training</option>
                            <option value="Mixed" <?php echo $shop['shop_category'] == 'Mixed' ? 'selected' : ''; ?>>General
                                Store</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Shop Address</label>
                        <textarea name="shop_address" class="form-control"
                            rows="3"><?php echo htmlspecialchars($shop['shop_address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Payment / Bank Details (For Settlements)</label>
                        <textarea name="bank_details" class="form-control" rows="2"
                            placeholder="Bank Name, Account #, or UPI ID"><?php echo htmlspecialchars($shop['bank_details'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Logo URL</label>
                        <input type="url" name="shop_logo" class="form-control"
                            value="<?php echo htmlspecialchars($shop['shop_logo'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Security & Account -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="settings-card">
                    <div class="card-title"><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> Security
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required
                                placeholder="Min 8 characters">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required
                                placeholder="Repeat new password">
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary"
                            style="background: #111827;">Update Password</button>
                    </form>
                </div>

                <div class="settings-card" style="border-color: #fee2e2; background: #fffcfc;">
                    <div class="card-title" style="color: #ef4444;"><i class="fa-solid fa-triangle-exclamation"></i>
                        Danger Zone</div>
                    <p style="font-size: 0.85rem; color: #991b1b; margin-bottom: 1.5rem;">Deactivating your shop will
                        hide all your products from the marketplace instantly.</p>
                    <button class="btn"
                        style="background: #fee2e2; color: #ef4444; border: 1px solid #ef4444;">Deactivate Shop</button>
                </div>
            </div>
        </div>
    </main>

</body>

</html>