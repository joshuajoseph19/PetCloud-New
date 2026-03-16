<?php
session_start();
require_once 'db_connect.php';

// Check if logged in and is shop owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop_owner') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch Listings for this user (who is a shop owner)
// We still use user_id because the listing is linked to the account creating it.
// If your schema strictly separates shop listings via shop_id, you might need to fetch shop_id first.
// For now, let's assume listings are tied to user_id even for shop owners, or we check both if we implemented shop_id logic.
$stmt = $pdo->prepare("SELECT * FROM adoption_listings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$myListings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Rehoming - Shop Manager</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f8fafc;
            --text-main: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-main);
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

            .header-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Reusing styles from pet-rehoming.php but adapting container */
        .rehome-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: 0.2s;
            margin-bottom: 1rem;
        }

        .rehome-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .pet-thumb {
            width: 80px;
            height: 80px;
            border-radius: 0.75rem;
            object-fit: cover;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending_approval {
            background: #fef3c7;
            color: #b45309;
        }

        .status-active {
            background: #ecfdf5;
            color: #047857;
        }

        .status-adopted {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 1rem;
            border: 2px dashed #e5e7eb;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .icon-btn {
            border: none;
            background: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: 0.2s;
        }

        .icon-btn:hover {
            background: #f1f5f9;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="header-flex">
            <div>
                <h2 style="font-family: 'Outfit'; font-size: 2rem; margin-bottom: 0.5rem;">Pet Rehoming Listings</h2>
                <p style="color: #64748b;">Manage pets you want to find homes for.</p>
            </div>
            <a href="adoption-list-pet.php?return=shop" class="btn-primary">
                <i class="fa-solid fa-plus"></i> List a New Pet
            </a>
        </div>

        <?php if (empty($myListings)): ?>
            <div class="empty-state">
                <div
                    style="width: 60px; height: 60px; background: #eef2ff; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem;">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">No pets listed yet</h3>
                <p style="color: #64748b; margin-bottom: 1.5rem;">List pets from your shop to help them find loving owners.
                </p>
                <a href="adoption-list-pet.php?return=shop" class="btn-primary">
                    Start Rehoming Process
                </a>
            </div>
        <?php else: ?>
            <div class="listings-list">
                <?php foreach ($myListings as $pet): ?>
                    <div class="rehome-card">
                        <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" class="pet-thumb">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <h3 style="font-size: 1.1rem; font-family: 'Outfit';">
                                    <?php echo htmlspecialchars($pet['pet_name']); ?>
                                </h3>
                                <span class="status-badge status-<?php echo $pet['status']; ?>">
                                    <?php echo str_replace('_', ' ', $pet['status']); ?>
                                </span>
                            </div>
                            <p style="color: #64748b; font-size: 0.9rem;">
                                <?php echo ucfirst($pet['pet_type']); ?> •
                                <?php echo htmlspecialchars($pet['breed']); ?>
                            </p>
                            <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">
                                Listed on
                                <?php echo date('M d, Y', strtotime($pet['created_at'])); ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <!-- Actions similar to user dashboard -->
                            <button class="icon-btn" title="View" style="color: #64748b;"><i
                                    class="fa-solid fa-eye"></i></button>
                            <button class="icon-btn" title="Delete" style="color: #ef4444;"><i
                                    class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>

</html>