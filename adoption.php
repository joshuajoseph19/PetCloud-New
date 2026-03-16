<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .pet-filters .btn-filter {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pet-filters .btn-filter.active {
            background: white;
            color: #111827;
            border: 2px solid #111827;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pet-card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pet-card:hover {
            transform: translateY(-5px);
        }

        .pet-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .pet-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .category-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-dog {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-cat {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rabbit {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-view-profile {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            margin-top: auto;
            transition: background 0.2s;
        }

        .btn-view-profile:hover {
            background: #059669;
        }

        .top-header {
            justify-content: center !important;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'user-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="pet-search" placeholder="Search for pets to adopt...">
                </div>
            </header>

            <div class="content-wrapper">
                <h1 style="margin-bottom: 2rem; font-family: 'Outfit', sans-serif; font-size: 2.5rem;">Find Your New
                    Best Friend</h1>

                <div class="pet-filters" style="display: flex; gap: 1rem; margin-bottom: 3rem;">
                    <button class="btn-filter active" data-filter="all">All Pets</button>
                    <button class="btn-filter" data-filter="dog">Dogs</button>
                    <button class="btn-filter" data-filter="cat">Cats</button>
                    <button class="btn-filter" data-filter="rabbit">Rabbits</button>
                    <button class="btn-filter" data-filter="bird">Birds</button>
                </div>

                <div class="pets-grid" id="pets-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2.5rem;">

                    <?php
                    // Fetch Active Listings
                    $sql = "SELECT * FROM adoption_listings WHERE status = 'active' ORDER BY created_at DESC";
                    $stmt = $pdo->query($sql);
                    $listings = $stmt->fetchAll();

                    if (empty($listings)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; color: #6b7280; padding: 4rem;">
                            <i class="fa-solid fa-paw" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                            <h3>No pets currently available for adoption.</h3>
                            <p>Check back later or list your own!</p>
                        </div>
                    <?php else:
                        foreach ($listings as $pet):
                            $badgeClass = 'badge-dog'; // logic to vary colors based on type
                            if ($pet['pet_type'] == 'cat')
                                $badgeClass = 'badge-cat';
                            if ($pet['pet_type'] == 'rabbit')
                                $badgeClass = 'badge-rabbit';
                            if ($pet['pet_type'] == 'bird')
                                $badgeClass = 'badge-dog'; // reuse blue
                            ?>
                            <!-- Pet Card -->
                            <div class="pet-card" data-category="<?php echo strtolower($pet['pet_type']); ?>">
                                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" class="pet-image"
                                    alt="<?php echo htmlspecialchars($pet['pet_name']); ?>">
                                <div class="pet-info">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                        <h3 style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">
                                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                                        </h3>
                                        <span
                                            class="category-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($pet['pet_type']); ?></span>
                                    </div>
                                    <p style="color: #6b7280; font-size: 1rem; margin-bottom: 2rem;">
                                        <?php echo htmlspecialchars($pet['age']); ?> •
                                        <?php echo htmlspecialchars($pet['breed'] ?: 'Unknown Breed'); ?>
                                    </p>
                                    <button class="btn-view-profile" onclick="viewPet(
                                        '<?php echo addslashes($pet['pet_name']); ?>',
                                        '<?php echo addslashes($pet['image_url']); ?>',
                                        '<?php echo addslashes($pet['pet_type']); ?>',
                                        '<?php echo addslashes($badgeClass); ?>',
                                        '<?php echo addslashes($pet['age'] . ' • ' . $pet['breed']); ?>',
                                        '<?php echo addslashes(str_replace(["\r", "\n"], ' ', $pet['description'])); ?>',
                                        '<?php echo addslashes($pet['gender']); ?>',
                                        '<?php echo $pet['id']; ?>'
                                    )">View Profile</button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>

                </div>
            </div>
        </main>
    </div>

    <!-- Pet Detail Modal -->
    <div id="petModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);">
        <div
            style="background: white; width: 90%; max-width: 800px; margin: 5vh auto; border-radius: 2rem; overflow: hidden; position: relative; animation: slideIn 0.3s ease-out;">
            <button onclick="closeModal()"
                style="position: absolute; right: 2rem; top: 2rem; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; z-index: 10;">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 500px;">
                <div id="modalImage" style="background-size: cover; background-position: center;"></div>
                <div style="padding: 3rem; display: flex; flex-direction: column;">
                    <div id="modalBadge" style="width: fit-content; margin-bottom: 1rem;"></div>
                    <h2 id="modalName"
                        style="font-size: 2.5rem; font-family: 'Outfit', sans-serif; margin-bottom: 0.5rem;"></h2>
                    <p id="modalInfo" style="color: #6b7280; font-size: 1.1rem; margin-bottom: 2rem;"></p>

                    <div style="margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 0.75rem;">About this pet</h4>
                        <p id="modalDesc" style="color: #4b5563; line-height: 1.6;"></p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: auto;">
                        <div style="background: #f9fafb; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <span
                                style="display: block; font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">Weight</span>
                            <span style="font-weight: 600;">5.4 kg</span>
                        </div>
                        <div style="background: #f9fafb; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <span
                                style="display: block; font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">Gender</span>
                            <span style="font-weight: 600;">Female</span>
                        </div>
                    </div>

                    <button id="modalAdoptBtn" class="btn-view-profile"
                        style="margin-top: 2rem; font-size: 1.1rem; padding: 1rem;">Adopt Me Today</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideIn {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        const searchInput = document.getElementById('pet-search');
        const filterButtons = document.querySelectorAll('.btn-filter');
        const petCards = document.querySelectorAll('.pet-card');
        const modal = document.getElementById('petModal');

        function viewPet(name, image, type, badgeClass, info, desc, gender, id) {
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalImage').style.backgroundImage = `url('${image}')`;
            document.getElementById('modalInfo').textContent = info;
            document.getElementById('modalDesc').textContent = desc;

            const badge = document.getElementById('modalBadge');
            badge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
            badge.className = 'category-badge ' + badgeClass;

            // Update Adopt button link with the real listing ID
            const adoptBtn = document.getElementById('modalAdoptBtn');
            adoptBtn.onclick = () => {
                window.location.href = `apply-adoption.php?listing_id=${id}`;
            };

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Stop scrolling
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal on click outside
        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Search and Filter logic (keep previous)
        searchInput.addEventListener('input', filterPets);
        filterButtons.forEach(btn => btn.addEventListener('click', () => {
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterPets();
        }));

        function filterPets() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeFilter = document.querySelector('.btn-filter.active').dataset.filter;

            petCards.forEach(card => {
                const petName = card.querySelector('h3').textContent.toLowerCase();
                const petInfo = card.querySelector('p').textContent.toLowerCase();
                const petCategory = card.dataset.category;

                const matchesSearch = petName.includes(searchTerm) || petInfo.includes(searchTerm);
                const matchesCategory = activeFilter === 'all' || petCategory === activeFilter;

                card.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
            });
        }
    </script>
</body>

</html>