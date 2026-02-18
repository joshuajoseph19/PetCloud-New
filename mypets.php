<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';
$success = "";
$error = "";

// --- AUTO-FIX: Create Table If Missing & Enhance Schema ---
try {
    $pdo->query("SELECT pet_gender FROM user_pets LIMIT 1");
} catch (PDOException $e) {
    // Table might exist but need columns, or it might not exist at all
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_pets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        pet_name VARCHAR(100) NOT NULL,
        pet_breed VARCHAR(100),
        pet_age VARCHAR(50),
        pet_type VARCHAR(50),
        pet_image TEXT,
        pet_gender VARCHAR(20) DEFAULT 'Unknown',
        pet_weight VARCHAR(20) DEFAULT '0 kg',
        pet_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Check if columns missing in existing table
    try {
        $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_gender VARCHAR(20) DEFAULT 'Unknown'");
    } catch (Exception $ex) {
    }
    try {
        $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_weight VARCHAR(20) DEFAULT '0 kg'");
    } catch (Exception $ex) {
    }
    try {
        $pdo->exec("ALTER TABLE user_pets ADD COLUMN pet_description TEXT");
    } catch (Exception $ex) {
    }
}

// Seed all pets if empty
$count = $pdo->prepare("SELECT COUNT(*) FROM user_pets WHERE user_id = ?");
$count->execute([$user_id]);
if ($count->fetchColumn() == 0) {
    $demoPets = [
        ['Rocky', 'Golden Retriever', '1.5 Years', 'Dog', 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600', 'Female', '5.4 kg', 'Rocky is a friendly and energetic Golden Retriever who loves long walks and playing fetch.'],
        ['Luna', 'Tabby', '8 Months', 'Cat', 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?w=600', 'Female', '2.7 kg', 'Luna is a sweet and curious kitten who loves to cuddle.'],
        ['Daisy', 'Dwarf Rabbit', '2 Years', 'Rabbit', 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=600', 'Female', '1.4 kg', 'Daisy is a gentle and quiet rabbit who enjoys munching on carrots and hay.'],
        ['Rio', 'Parrot', '1 Year', 'Bird', 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=600', 'Male', '0.5 kg', 'Rio is a very intelligent and talkative Parrot who loves to whistle.'],
        ['Max', 'Beagle', '3 Years', 'Dog', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=600', 'Male', '9.1 kg', 'Max is a classic Beagle with an amazing sense of smell and a friendly heart.'],
        ['Simba', 'Ginger Tabby', '2 Years', 'Cat', 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=600', 'Male', '4.5 kg', 'Simba is a majestic ginger cat who thinks he is the king of the house.']
    ];

    $insert = $pdo->prepare("INSERT INTO user_pets (user_id, pet_name, pet_breed, pet_age, pet_type, pet_image, pet_gender, pet_weight, pet_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($demoPets as $p) {
        $insert->execute(array_merge([$user_id], $p));
    }
}

// --- Handle Add Pet Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_pet'])) {
        $name = $_POST['pet_name'];
        $breed = $_POST['pet_breed'];
        $age = $_POST['pet_age'];
        $type = $_POST['pet_type'];

        // Default image if none provided
        $image = "https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=600&h=600&fit=crop";

        $gender = $_POST['pet_gender'] ?? 'Unknown';
        $weight = trim($_POST['pet_weight']) ?: '0 kg';
        $desc = $_POST['pet_description'] ?? '';

        try {
            $stmt = $pdo->prepare("INSERT INTO user_pets (user_id, pet_name, pet_breed, pet_age, pet_type, pet_image, pet_gender, pet_weight, pet_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $name, $breed, $age, $type, $image, $gender, $weight, $desc])) {
                $success = "New family member added! 🐾";
            }
        } catch (PDOException $e) {
            $error = "Error adding pet: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_pet'])) {
        $pet_id = $_POST['pet_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM user_pets WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$pet_id, $user_id])) {
                $success = "Pet removed successfully.";
            }
        } catch (PDOException $e) {
            $error = "Error removing pet: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_pet'])) {
        $pet_id = $_POST['pet_id'];
        $name = $_POST['pet_name'];
        $breed = $_POST['pet_breed'];
        $age = $_POST['pet_age'];
        $type = $_POST['pet_type'];
        $gender = $_POST['pet_gender'];
        $weight = $_POST['pet_weight'];
        $desc = $_POST['pet_description'];

        try {
            $sql = "UPDATE user_pets SET pet_name=?, pet_breed=?, pet_age=?, pet_type=?, pet_gender=?, pet_weight=?, pet_description=? WHERE id=? AND user_id=?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $breed, $age, $type, $gender, $weight, $desc, $pet_id, $user_id])) {
                $success = "Pet details updated successfully! 🐾";
            }
        } catch (PDOException $e) {
            $error = "Error updating pet: " . $e->getMessage();
        }
    }
}

// --- Handle Mark as Found from mypets ---
if (isset($_POST['mark_as_found'])) {
    $pet_id = $_POST['pet_id'];
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE user_pets SET status = 'Active' WHERE id = ? AND user_id = ?")->execute([$pet_id, $user_id]);
    $pdo->prepare("UPDATE lost_pet_alerts SET status = 'Resolved' WHERE pet_id = ? AND status = 'Active'")->execute([$pet_id]);
    $pdo->commit();
    $success = "Welcome home! Pet marked as found.";
}

// --- Fetch User Pets ---
$stmt = $pdo->prepare("SELECT * FROM user_pets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .pet-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .pet-card:hover {
            transform: translateY(-5px);
        }

        .pet-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f3f4f6;
        }

        .pet-card.lost {
            border: 2px solid #ef4444;
            background: #fffafa;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            text-transform: uppercase;
        }

        .add-card {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 1.5rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            color: #6b7280;
        }

        .add-card:hover {
            border-color: #10b981;
            background: #f0fdf4;
            color: #10b981;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 10vh auto;
            border-radius: 1.5rem;
            padding: 2.5rem;
            position: relative;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
        }

        .form-group input:focus {
            border-color: #10b981;
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
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="petSearch" placeholder="Search your pets...">
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal()"
                        style="padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i> Add New Pet
                    </button>
                </div>
            </header>

            <div class="content-wrapper">
                <h1 style="margin-bottom: 2rem; font-family: 'Outfit';">My Pets</h1>

                <?php if ($success): ?>
                    <div
                        style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 2rem;">

                    <?php foreach ($pets as $pet):
                        $isLost = ($pet['status'] === 'Lost');
                        ?>
                        <!-- Pet Card -->
                        <div class="pet-card <?php echo $isLost ? 'lost' : ''; ?>"
                            data-name="<?php echo strtolower($pet['pet_name']); ?>" style="position: relative;">
                            <?php if ($isLost): ?>
                                <span class="status-badge">Lost</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-avatar" alt="Pet"
                                style="<?php echo $isLost ? 'filter: grayscale(0.5);' : ''; ?>">
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">
                                    <?php echo htmlspecialchars($pet['pet_name']); ?>
                                </h3>
                                <p style="color: #6b7280; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($pet['pet_breed']); ?> •
                                    <?php echo htmlspecialchars($pet['pet_age']); ?>
                                </p>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button class="btn btn-sm btn-outline" style="padding: 0.4rem 1rem;"
                                        onclick="openDetailModal(<?php echo $pet['id']; ?>)">Profile</button>

                                    <?php if ($isLost): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                            <button type="submit" name="mark_as_found" class="btn btn-sm"
                                                style="padding: 0.4rem 1rem; background: #10b981; color: white; border: none; font-weight: 700;">Found!</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline"
                                            style="padding: 0.4rem 1rem; color: #ef4444; border-color: #fecaca;"
                                            onclick="openLostModal(<?php echo $pet['id']; ?>, '<?php echo htmlspecialchars($pet['pet_name']); ?>')">Report
                                            Lost</button>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-outline" style="padding: 0.4rem 1rem;"
                                        onclick="window.location.href='health-records.php?pet_id=<?php echo $pet['id']; ?>'">Health</button>
                                    <form method="POST"
                                        onsubmit="return confirm('Are you sure you want to remove <?php echo htmlspecialchars($pet['pet_name']); ?>? This action cannot be undone.');"
                                        style="display:inline;">
                                        <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                        <button type="submit" name="delete_pet" class="btn btn-sm btn-outline"
                                            style="padding: 0.4rem 0.8rem; color: #ef4444; border-color: #fee2e2;"
                                            title="Remove Pet">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Add Pet Card -->
                    <div class="add-card" onclick="openModal()">
                        <i class="fa-solid fa-circle-plus"
                            style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="font-weight: 600; font-size: 1.1rem;">Add another family member</p>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Add Pet Modal -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <h2 style="font-family: 'Outfit'; margin-bottom: 1.5rem;">Add New Pet</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Pet Name</label>
                    <input type="text" name="pet_name" required placeholder="e.g. Buddy">
                </div>
                <div class="form-group">
                    <label>Pet Type</label>
                    <select name="pet_type" required>
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Bird">Bird</option>
                        <option value="Rabbit">Rabbit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="pet_breed" required placeholder="e.g. Golden Retriever">
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="text" name="pet_age" required placeholder="e.g. 2 Years">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="pet_gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Weight</label>
                    <input type="text" name="pet_weight" placeholder="e.g. 5 kg">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="pet_description" rows="3" placeholder="Tell us about your pet..."
                        style="width: 100%; padding: 0.75rem; border: 1.5px solid #e5e7eb; border-radius: 0.75rem; font-family: inherit;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn btn-outline" style="flex: 1;"
                        onclick="closeModal()">Cancel</button>
                    <button type="submit" name="add_pet" class="btn btn-primary" style="flex: 1;">Save Pet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pet Detail Modal -->
    <div id="petDetailModal" class="modal">
        <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden; border-radius: 2rem;">
            <div style="display: flex; min-height: 400px;">
                <div style="flex: 1; background: #f3f4f6;">
                    <img id="detailPetImage" src="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1.2; padding: 2.5rem; display: flex; flex-direction: column; position: relative;">
                    <div style="position: absolute; right: 1.5rem; top: 1.5rem; display: flex; gap: 0.5rem;">
                        <button onclick="openEditModalFromDetail()"
                            style="background: #e0f2fe; color: #0369a1; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                            title="Edit Pet">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button onclick="closeDetailModal()"
                            style="background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <span id="detailPetType"
                            style="background: #e0f2fe; color: #0369a1; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;"></span>
                        <h2 id="detailPetName"
                            style="font-family: 'Outfit'; font-size: 2.5rem; margin-top: 0.5rem; margin-bottom: 0.25rem;">
                        </h2>
                        <p id="detailPetBreed" style="color: #6b7280; font-size: 1rem;"></p>
                        <p id="detailPetAge" style="color: #9ca3af; font-size: 0.85rem;"></p>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <h4 style="font-family: 'Outfit'; margin-bottom: 0.75rem;">About this pet</h4>
                        <p id="detailPetDescription" style="color: #4b5563; font-size: 0.9rem; line-height: 1.6;"></p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                        <div
                            style="background: #f9fafb; padding: 1rem; border-radius: 1rem; text-align: center; border: 1px solid #f3f4f6;">
                            <span
                                style="display: block; font-size: 0.7rem; color: #9ca3af; margin-bottom: 0.25rem; text-transform: uppercase; font-weight: 700;">Weight</span>
                            <span id="detailPetWeight"
                                style="font-weight: 700; font-size: 1.1rem; color: #1f2937;"></span>
                        </div>
                        <div
                            style="background: #f9fafb; padding: 1rem; border-radius: 1rem; text-align: center; border: 1px solid #f3f4f6;">
                            <span
                                style="display: block; font-size: 0.7rem; color: #9ca3af; margin-bottom: 0.25rem; text-transform: uppercase; font-weight: 700;">Gender</span>
                            <span id="detailPetGender"
                                style="font-weight: 700; font-size: 1.1rem; color: #1f2937;"></span>
                        </div>
                    </div>

                    <div style="margin-top: auto; display: flex; gap: 1rem;">
                        <button class="btn btn-primary" style="flex: 1; padding: 1rem; border-radius: 1rem;"
                            onclick="window.location.href='schedule.php'">Schedule Vet</button>
                        <button class="btn btn-outline" style="flex: 1; padding: 1rem; border-radius: 1rem;"
                            id="detail_health_btn" onclick="window.location.href='health-records.php'">Health
                            Records</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Pet Modal -->
    <div id="editPetModal" class="modal">
        <div class="modal-content">
            <h2 style="font-family: 'Outfit'; margin-bottom: 1.5rem;">Edit Pet Details</h2>
            <form method="POST">
                <input type="hidden" name="pet_id" id="edit_pet_id">
                <div class="form-group">
                    <label>Pet Name</label>
                    <input type="text" name="pet_name" id="edit_pet_name" required>
                </div>
                <div class="form-group">
                    <label>Pet Type</label>
                    <select name="pet_type" id="edit_pet_type" required>
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Bird">Bird</option>
                        <option value="Rabbit">Rabbit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="pet_breed" id="edit_pet_breed" required>
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="text" name="pet_age" id="edit_pet_age" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="pet_gender" id="edit_pet_gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Weight</label>
                    <input type="text" name="pet_weight" id="edit_pet_weight">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="pet_description" id="edit_pet_description" rows="3"
                        style="width: 100%; padding: 0.75rem; border: 1.5px solid #e5e7eb; border-radius: 0.75rem; font-family: inherit;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn btn-outline" style="flex: 1;"
                        onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_pet" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addModal = document.getElementById('addPetModal');
        const detailModal = document.getElementById('petDetailModal');
        const editModal = document.getElementById('editPetModal');
        const petSearch = document.getElementById('petSearch');

        // Use PHP to inject pet data for JS access
        const myPetsData = <?php echo json_encode($pets); ?>;

        function openModal() {
            addModal.style.display = 'block';
        }

        function closeModal() {
            addModal.style.display = 'none';
        }

        function openDetailModal(petId) {
            const pet = myPetsData.find(p => p.id == petId);
            if (!pet) return;

            document.getElementById('detailPetImage').src = pet.pet_image;
            document.getElementById('detailPetName').textContent = pet.pet_name;
            document.getElementById('detailPetBreed').textContent = pet.pet_breed;
            document.getElementById('detailPetAge').textContent = pet.pet_age;
            document.getElementById('detailPetType').textContent = pet.pet_type || 'Pet';

            // New fields from image
            document.getElementById('detailPetWeight').textContent = pet.pet_weight || 'Unknown';
            document.getElementById('detailPetGender').textContent = pet.pet_gender || 'Unknown';
            document.getElementById('detailPetDescription').textContent = pet.pet_description || 'No description available.';

            // Setup Edit Button in Detail Modal if we added one (or we can call openEditModal directly)
            // Let's add an Edit button dynamically or ensure it exists in HTML
            // For now, let's expose specific pet data to potential edit button
            window.currentPetId = petId;

            document.getElementById('detail_health_btn').onclick = function () {
                window.location.href = 'health-records.php?pet_id=' + pet.id;
            };

            detailModal.style.display = 'block';
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function openEditModalFromDetail() {
            if (!window.currentPetId) return;
            closeDetailModal();
            openEditModal(window.currentPetId);
        }

        function openEditModal(petId) {
            const pet = myPetsData.find(p => p.id == petId);
            if (!pet) return;

            document.getElementById('edit_pet_id').value = pet.id;
            document.getElementById('edit_pet_name').value = pet.pet_name;
            document.getElementById('edit_pet_type').value = pet.pet_type;
            document.getElementById('edit_pet_breed').value = pet.pet_breed;
            document.getElementById('edit_pet_age').value = pet.pet_age;
            document.getElementById('edit_pet_gender').value = pet.pet_gender || 'Unknown';
            document.getElementById('edit_pet_weight').value = pet.pet_weight || '';
            document.getElementById('edit_pet_description').value = pet.pet_description || '';

            editModal.style.display = 'block';
        }

        function closeEditModal() {
            editModal.style.display = 'none';
        }

        // Close on click outside
        window.onclick = (e) => {
            if (e.target == addModal) closeModal();
            if (e.target == detailModal) closeDetailModal();
            if (e.target == editModal) closeEditModal();
        }

        // Search functionality
        petSearch.addEventListener('input', () => {
            const term = petSearch.value.toLowerCase();
            document.querySelectorAll('.pet-card').forEach(card => {
                const name = card.dataset.name;
                card.style.display = name.includes(term) ? 'flex' : 'none';
            });
        });
    </script>

    <!-- Mark as Lost Modal -->
    <div id="lostModal" class="modal">
        <div class="modal-content">
            <h2 style="font-family: 'Outfit'; margin-bottom: 0.5rem; color: #ef4444;">Mark <span
                    id="lostPetName"></span> as Lost</h2>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 2rem;">Help us bring them home. Nearby users
                will be alerted.</p>
            <form id="lostPetForm" onsubmit="submitLostPet(event)">
                <input type="hidden" id="lostPetId" name="pet_id">
                <div class="form-group">
                    <label>Last Seen Location</label>
                    <input type="text" name="last_seen_location" required placeholder="e.g. Near Central Park">
                </div>
                <div class="form-group">
                    <label>Last Seen Date</label>
                    <input type="date" name="last_seen_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Description (Features, Collar, etc.)</label>
                    <textarea name="description" rows="3"
                        style="width: 100%; padding: 0.75rem; border: 1.5px solid #e5e7eb; border-radius: 0.75rem;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn btn-outline" style="flex: 1;"
                        onclick="closeLostModal()">Cancel</button>
                    <button type="submit" class="btn"
                        style="flex: 1; background: #ef4444; color: white; font-weight: 700; border: none; cursor: pointer;">Broadcast
                        Alert</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLostModal(id, name) {
            document.getElementById('lostPetId').value = id;
            document.getElementById('lostPetName').textContent = name;
            document.getElementById('lostModal').style.display = 'block';
        }
        function closeLostModal() {
            document.getElementById('lostModal').style.display = 'none';
        }
        async function submitLostPet(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api/mark_pet_lost.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }
    </script>
</body>

</html>