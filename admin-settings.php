<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

$success = "";
$error = "";

// --- Handle Saving ---
if (isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();

        $settings = [
            'commission_rate' => $_POST['commission'],
            'min_payout' => $_POST['min_payout'],
            'tax_rate' => $_POST['tax_rate'],
            'maintenance_mode' => isset($_POST['maint_mode']) ? '1' : '0',
            'auto_approve_shops' => isset($_POST['auto_shop']) ? '1' : '0',
            'public_adoption' => isset($_POST['public_adoption']) ? '1' : '0',
        ];

        foreach ($settings as $key => $val) {
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $val, $val]);
        }

        logAdminActivity($pdo, $_SESSION['admin_name'] ?? 'Admin', "Updated global platform settings", "Settings");
        $pdo->commit();
        $success = "Global platform settings updated successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Update failed: " . $e->getMessage();
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Fetch Settings ---
$dbSettings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fallbacks if DB is missing keys
$s = array_merge([
    'commission_rate' => '10',
    'min_payout' => '50',
    'tax_rate' => '2.5',
    'maintenance_mode' => '0',
    'auto_approve_shops' => '0',
    'public_adoption' => '1'
], $dbSettings);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>System Settings - Admin Panel</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --sidebar: #111827;
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

        .main-layout {
            margin-left: 260px;
            padding: 2.5rem;
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .admin-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            font-size: 0.875rem;
        }

        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary);
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .btn-save {
            background: #111827;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 2rem;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: #000;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <h2 class="page-title">General Platform Settings</h2>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="settings-grid">
                <div class="admin-card">
                    <div class="section-title"><i class="fa-solid fa-comment-dollar"></i> Marketplace Economics</div>
                    <div class="form-group">
                        <label class="form-label">Platform Sales Commission (%)</label>
                        <input type="number" name="commission" class="form-input"
                            value="<?php echo htmlspecialchars($s['commission_rate']); ?>" step="0.1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minimum Payout Amount (₹)</label>
                        <input type="number" name="min_payout" class="form-input"
                            value="<?php echo htmlspecialchars($s['min_payout']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Rate / Handling Fee (%)</label>
                        <input type="number" name="tax_rate" class="form-input"
                            value="<?php echo htmlspecialchars($s['tax_rate']); ?>" step="0.1">
                    </div>
                </div>

                <div class="admin-card">
                    <div class="section-title"><i class="fa-solid fa-shield-halved"></i> Global Controls</div>
                    <div class="toggle-switch">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.875rem;">Maintenance Mode</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Lock the platform for scheduled updates
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="maint_mode" <?php echo $s['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-switch">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.875rem;">Auto-Approve Shops</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Skip the manual review process</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="auto_shop" <?php echo $s['auto_approve_shops'] == '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-switch">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.875rem;">Public Adoption List</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Allow guest visitors to see pets</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="public_adoption" <?php echo $s['public_adoption'] == '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div style="max-width: 400px; margin: 4rem auto 0;">
                <button type="submit" name="save_settings" class="btn-save">Save All Configuration</button>
            </div>
        </form>
    </main>
</body>

</html>