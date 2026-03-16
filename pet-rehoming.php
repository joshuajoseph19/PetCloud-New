<?php
session_start();
require_once 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';

// Fetch User's Listings
$stmt = $pdo->prepare("SELECT * FROM adoption_listings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$myListings = $stmt->fetchAll();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['listing_action'])) {
    $lid = $_POST['listing_id'];
    $action = $_POST['listing_action'];

    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM adoption_listings WHERE id = ? AND user_id = ?");
    $check->execute([$lid, $user_id]);
    if ($check->fetch()) {
        if ($action == 'delete') {
            $pdo->prepare("DELETE FROM adoption_listings WHERE id = ?")->execute([$lid]);
        } elseif ($action == 'mark_adopted') {
            $pdo->prepare("UPDATE adoption_listings SET status = 'adopted' WHERE id = ?")->execute([$lid]);
        }
        header("Location: pet-rehoming.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pet Rehoming - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .rehome-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            border: 1px solid #f3f4f6;
            transition: 0.2s;
            margin-bottom: 1rem;
        }

        .rehome-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .pet-thumb {
            width: 100px;
            height: 100px;
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
            background: #dcfce7;
            color: #166534;
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
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'user-sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="header-actions">
                    <a href="adoption-list-pet.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Rehome a New Pet
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <section style="margin-bottom: 2rem;">
                    <h1 style="font-family: 'Outfit'; font-size: 2rem; color: #111827; margin-bottom: 0.5rem;">My
                        Rehoming Listings</h1>
                    <p style="color: #6b7280;">Manage the pets you have listed for adoption.</p>
                </section>

                <?php if (empty($myListings)): ?>
                    <div class="empty-state">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4085812-3385481.png"
                            style="width: 200px; opacity: 0.5; margin-bottom: 1rem;">
                        <h3 style="font-size: 1.5rem; color: #374151; margin-bottom: 0.5rem;">No pets listed yet</h3>
                        <p style="color: #6b7280; margin-bottom: 2rem;">Help a pet find a loving new home today.</p>
                        <a href="adoption-list-pet.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                            Start Rehoming Process
                        </a>
                    </div>
                <?php else: ?>
                    <div class="listings-list">
                        <?php foreach ($myListings as $pet): ?>
                            <div class="rehome-card">
                                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" class="pet-thumb">
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <h3 style="font-size: 1.25rem; font-family: 'Outfit';">
                                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                                        </h3>
                                        <span class="status-badge status-<?php echo $pet['status']; ?>">
                                            <?php echo str_replace('_', ' ', $pet['status']); ?>
                                        </span>
                                    </div>
                                    <p style="color: #6b7280; font-size: 0.9rem;">
                                        <?php echo ucfirst($pet['pet_type']); ?> •
                                        <?php echo htmlspecialchars($pet['breed']); ?> •
                                        <?php echo htmlspecialchars($pet['age']); ?>
                                    </p>
                                    <div style="font-size: 0.8rem; color: #9ca3af; margin-top: 0.5rem;">
                                        Listed on
                                        <?php echo date('M d, Y', strtotime($pet['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($pet['status'] == 'active'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="listing_id" value="<?php echo $pet['id']; ?>">
                                            <input type="hidden" name="listing_action" value="mark_adopted">
                                            <button type="submit" class="icon-btn" title="Mark Adopted"
                                                style="color: #10b981; border: 1px solid #d1fae5; background: #ecfdf5; cursor: pointer;">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="display: inline;"
                                        onsubmit="return confirm('Are you sure you want to remove this listing?');">
                                        <input type="hidden" name="listing_id" value="<?php echo $pet['id']; ?>">
                                        <input type="hidden" name="listing_action" value="delete">
                                        <button type="submit" class="icon-btn" title="Delete"
                                            style="color: #ef4444; border: 1px solid #fee2e2; background: #fffcfc; cursor: pointer;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>