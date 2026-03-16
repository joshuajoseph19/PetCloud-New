<?php
// browse-adoptions.php - Updated to match "Find Your New Best Friend" design
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your New Best Friend - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css"> <!-- Include main styles -->
    <style>
        :root {
            --primary: #4f46e5;
            --text-dark: #111827;
            --text-gray: #6b7280;
            --bg-light: #f9fafb;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        /* Layout to match dashboard if needed, but keeping it standalone-ish for now */
        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header Section */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        /* Filter Pills */
        .filter-pills {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .filter-pill {
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: var(--text-gray);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-pill:hover {
            border-color: var(--text-dark);
            color: var(--text-dark);
        }

        .filter-pill.active {
            background: #fff;
            /* Design shows white background with dark border for active */
            border: 2px solid var(--text-dark);
            color: var(--text-dark);
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Grid */
        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* Matches the Screenshot Card Style */
        .pet-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
        }

        .pet-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .pet-image-container {
            width: 100%;
            height: 240px;
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
        }

        .pet-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pet-content {
            padding: 0.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .pet-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .pet-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .pet-badge {
            padding: 0.25rem 0.75rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-cat {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rabbit {
            background: #d1fae5;
            color: #065f46;
        }

        .pet-meta {
            color: var(--text-gray);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .view-btn {
            margin-top: auto;
            width: 100%;
            padding: 0.875rem;
            background: #10b981;
            /* Green color from screenshot */
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-align: center;
        }

        .view-btn:hover {
            background: #059669;
        }

        /* Search Bar (Top Right Overlay style or in header) */
        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .search-container {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            font-family: inherit;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .loading,
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem;
            color: var(--text-gray);
        }
    </style>
</head>

<body>

    <!-- Assuming Sidebar is included in typical layout, specifically user-sidebar.php -->
    <div style="display: flex;">
        <?php include 'user-sidebar.php'; ?>

        <div style="flex-grow: 1; height: 100vh; overflow-y: auto;">
            <div class="page-container">

                <div class="top-bar">
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search for pets to adopt..."
                            id="searchInput">
                    </div>
                </div>

                <div class="page-header">
                    <h1>Find Your New Best Friend</h1>
                </div>

                <!-- Filter Pills -->
                <div class="filter-pills">
                    <button class="filter-pill active" onclick="applyFilter('')">All Pets</button>
                    <button class="filter-pill" onclick="applyFilter('dog')">Dogs</button>
                    <button class="filter-pill" onclick="applyFilter('cat')">Cats</button>
                    <button class="filter-pill" onclick="applyFilter('rabbit')">Rabbits</button>
                    <button class="filter-pill" onclick="applyFilter('bird')">Birds</button>
                </div>

                <!-- Pets Grid -->
                <div class="pets-grid" id="petsGrid">
                    <!-- Loaded via API -->
                    <div class="loading">
                        <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                        <p style="margin-top: 1rem">Finding friends...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        let currentFilter = '';

        document.addEventListener('DOMContentLoaded', () => {
            loadPets();

            // Search listener
            let debounceTimer;
            document.getElementById('searchInput').addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    // For now, client side filtering or implement API search
                    // Reload with city/state if needed, but let's stick to type filter for MVP
                    loadPets();
                }, 300);
            });
        });

        function applyFilter(type) {
            currentFilter = type;

            // Update UI
            document.querySelectorAll('.filter-pill').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(type) || (type === '' && btn.textContent === 'All Pets')) { // Simple match logic
                    btn.classList.add('active');
                } else if (type === 'dog' && btn.textContent === 'Dogs') {
                    btn.classList.add('active');
                } else if (type === 'cat' && btn.textContent === 'Cats') {
                    btn.classList.add('active');
                } else if (type === 'rabbit' && btn.textContent === 'Rabbits') {
                    btn.classList.add('active');
                } else if (type === 'bird' && btn.textContent === 'Birds') {
                    btn.classList.add('active');
                }
            });

            loadPets();
        }

        async function loadPets() {
            const grid = document.getElementById('petsGrid');
            grid.innerHTML = '<div class="loading"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p style="margin-top: 1rem">Finding friends...</p></div>';

            try {
                let url = 'api/get_adoption_listings.php?limit=20'; // Increased limit

                if (currentFilter) {
                    // Pass pet_type slug directly, now supported by API
                    url += `&pet_type=${currentFilter}`;
                }

                // check search
                const search = document.getElementById('searchInput').value;
                if (search) {
                    // If the API supports search, add it. Currently it supports 'city' property search via 'city'
                    // For now, let's just rely on filters as search logic isn't fully expanded in API yet
                }

                const res = await fetch(url);
                const json = await res.json();

                if (json.success) {
                    renderPets(json.data);
                } else {
                    grid.innerHTML = `<div class="empty-state"><p>Error: ${json.error}</p></div>`;
                }

            } catch (e) {
                console.error(e);
                grid.innerHTML = '<div class="empty-state"><p>Something went wrong loading pets.</p></div>';
            }
        }

        function renderPets(pets) {
            const grid = document.getElementById('petsGrid');

            if (pets.length === 0) {
                grid.innerHTML = '<div class="empty-state"><p>No pets found matching your criteria.</p></div>';
                return;
            }

            grid.innerHTML = pets.map(pet => {
                // Determine badge class
                let badgeClass = 'pet-badge';
                const typeLower = pet.pet_type.name.toLowerCase();
                if (typeLower === 'cat') badgeClass += ' badge-cat';
                else if (typeLower === 'rabbit') badgeClass += ' badge-rabbit';
                else if (typeLower === 'bird') badgeClass += ' badge-cat'; // Fallback to yellow-ish or custom

                return `
            <div class="pet-card">
                <div class="pet-image-container">
                    <img src="${pet.image || 'images/placeholder-pet.jpg'}" class="pet-image" alt="${pet.pet_name}">
                </div>
                <div class="pet-content">
                    <div class="pet-header">
                        <h3 class="pet-name">${pet.pet_name}</h3>
                        <span class="${badgeClass}">${pet.pet_type.name}</span>
                    </div>
                    <div class="pet-meta">
                        ${pet.age.display} • ${pet.breed.name}
                    </div>
                    <button class="view-btn" onclick="window.location.href='pet-details.php?id=${pet.id}'">View Profile</button>
                </div>
            </div>
            `;
            }).join('');
        }
    </script>

</body>

</html>