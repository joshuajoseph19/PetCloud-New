<?php
session_start();
require_once 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $petName = $_POST['pet_name'] ?? '';
    $petType = $_POST['pet_type'] ?? '';
    $breed = $_POST['breed'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? 'Unknown';
    $desc = $_POST['description'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Image Upload Handling (Simplified for MVP)
    $imageUrl = 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600'; // Default fallback

    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === 0) {
        $uploadDir = 'images/uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['pet_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $targetPath)) {
            $imageUrl = $targetPath;
        }
    }

    try {
        $sql = "INSERT INTO adoption_listings (user_id, pet_name, pet_type, breed, age, gender, description, reason_for_adoption, image_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_approval')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $petName, $petType, $breed, $age, $gender, $desc, $reason, $imageUrl]);
        $success = true;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Pet - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --text-dark: #111827;
            --text-gray: #6b7280;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: #f9fafb;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wizard-container {
            background: white;
            width: 100%;
            max-width: 600px;
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .wizard-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
        }

        .wizard-progress {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .progress-dot {
            width: 8px;
            height: 8px;
            background: var(--gray-200);
            border-radius: 50%;
            transition: 0.3s;
        }

        .progress-dot.active {
            background: var(--primary);
            transform: scale(1.2);
        }

        .progress-dot.completed {
            background: var(--primary);
        }

        .form-step {
            display: none;
            padding: 0 2rem 2rem;
            animation: fadeIn 0.4s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        p.subtitle {
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        /* Step 1: Type Selection */
        .type-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .type-option {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }

        .type-option:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .type-option.selected {
            border-color: var(--primary);
            background: var(--primary-light);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .type-option i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        /* Step 2: Inputs */
        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .input-field {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary);
        }

        .gender-options {
            display: flex;
            gap: 1rem;
        }

        .pill-option {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
        }

        .pill-option.selected {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }

        /* Step 3: File Upload */
        .upload-zone {
            border: 2px dashed var(--gray-200);
            border-radius: 1rem;
            padding: 3rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: #fafafa;
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--gray-200);
            margin-bottom: 1rem;
        }

        .file-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 0.5rem;
            display: none;
            margin: 0 auto;
        }

        /* Controls */
        .wizard-footer {
            padding: 1rem 2rem 2rem;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-prev {
            background: transparent;
            color: var(--text-gray);
        }

        .btn-prev:hover {
            color: var(--text-dark);
        }

        .btn-next {
            background: var(--text-dark);
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-next:hover {
            background: #000;
            transform: translateY(-2px);
        }

        /* Success State */
        .success-animation {
            text-align: center;
            padding: 2rem;
        }

        .checkmark-circle {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            color: #059669;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        /* Breed Searchable Dropdown */
        .breed-container {
            position: relative;
        }

        .breed-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            margin-top: 0.5rem;
            max-height: 250px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .breed-group-label {
            padding: 0.75rem 1rem 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f9fafb;
        }

        .breed-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: 0.2s;
            font-size: 0.9375rem;
            color: var(--text-dark);
        }

        .breed-item:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .no-results {
            padding: 1rem;
            text-align: center;
            color: var(--text-gray);
            font-size: 0.875rem;
        }
    </style>
</head>

<body>

    <div class="wizard-container">
        <?php if ($success): ?>
            <div class="success-animation">
                <div class="checkmark-circle"><i class="fa-solid fa-check"></i></div>
                <h2>Listing Submitted!</h2>
                <p class="subtitle">Your pet is now pending approval by our admin team. You can track the status in your
                    dashboard.</p>
                <?php
                $returnPage = (isset($_GET['return']) && $_GET['return'] == 'shop') ? 'shop-pet-rehoming.php' : 'dashboard.php';
                ?>
                <button onclick="window.location.href='<?php echo $returnPage; ?>'" class="btn btn-next"
                    style="width:100%; justify-content:center;">Back to Dashboard</button>
            </div>
        <?php else: ?>

            <form id="listingForm" method="POST" enctype="multipart/form-data">
                <div class="wizard-header">
                    <h2 id="stepTitle">Let's get started</h2>
                    <p class="subtitle" id="stepSub">Tell us about the pet you want to rehome.</p>
                    <div class="wizard-progress">
                        <div class="progress-dot active" data-step="1"></div>
                        <div class="progress-dot" data-step="2"></div>
                        <div class="progress-dot" data-step="3"></div>
                        <div class="progress-dot" data-step="4"></div>
                    </div>
                </div>

                <!-- STEP 1: Type -->
                <div class="form-step active" data-step="1">
                    <input type="hidden" name="pet_type" id="pet_type" required>
                    <div class="type-grid">
                        <div class="type-option" onclick="selectType(this, 'dog')">
                            <i class="fa-solid fa-dog"></i>
                            <div>Dog</div>
                        </div>
                        <div class="type-option" onclick="selectType(this, 'cat')">
                            <i class="fa-solid fa-cat"></i>
                            <div>Cat</div>
                        </div>
                        <div class="type-option" onclick="selectType(this, 'rabbit')">
                            <i class="fa-solid fa-carrot"></i>
                            <div>Rabbit</div>
                        </div>
                        <div class="type-option" onclick="selectType(this, 'bird')">
                            <i class="fa-solid fa-dove"></i>
                            <div>Bird</div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Basic Info -->
                <div class="form-step" data-step="2">
                    <div class="input-group">
                        <label>Pet's Name</label>
                        <input type="text" name="pet_name" class="input-field" placeholder="e.g. Bella" required>
                    </div>
                    <div class="input-group">
                        <label>Gender</label>
                        <input type="hidden" name="gender" id="gender_input">
                        <div class="gender-options">
                            <div class="pill-option" onclick="selectGender(this, 'Male')">Male</div>
                            <div class="pill-option" onclick="selectGender(this, 'Female')">Female</div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Approximate Age</label>
                        <input type="text" name="age" class="input-field" placeholder="e.g. 2 years" required>
                    </div>
                </div>

                <!-- STEP 3: Details -->
                <div class="form-step" data-step="3">
                    <div class="input-group">
                        <label>Breed (Optional)</label>
                        <div class="breed-container">
                            <input type="text" name="breed" id="breedInput" class="input-field"
                                placeholder="e.g. Golden Retriever" autocomplete="off" oninput="filterBreeds(this.value)"
                                onfocus="showSuggestions()">
                            <div id="breedSuggestions" class="breed-suggestions">
                                <!-- Suggestions will be populated by JS -->
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Description</label>
                        <textarea name="description" class="input-field" rows="3"
                            placeholder="Tell us about their personality..." style="resize:none;"></textarea>
                    </div>
                    <div class="input-group">
                        <label>Reason for Adoption</label>
                        <textarea name="reason" class="input-field" rows="2" placeholder="Why are you rehoming them?"
                            style="resize:none;"></textarea>
                    </div>
                </div>

                <!-- STEP 4: Photo -->
                <div class="form-step" data-step="4">
                    <div class="input-group">
                        <label>Upload a Photo</label>
                        <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <p>Click to upload a clear photo</p>
                            <img id="preview" class="file-preview">
                        </div>
                        <input type="file" name="pet_image" id="fileInput" accept="image/*" style="display:none;"
                            onchange="showPreview(this)">
                    </div>
                </div>

                <div class="wizard-footer">
                    <button type="button" class="btn btn-prev" id="prevBtn" onclick="changeStep(-1)" disabled>Back</button>
                    <button type="button" class="btn btn-next" id="nextBtn" onclick="changeStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;
        const form = document.getElementById('listingForm');

        const titles = {
            1: ["Let's get started", "What kind of pet is it?"],
            2: [" The Basics ", "Tell us a bit about them."],
            3: ["Almost done", "Share some details for the adopter."],
            4: ["Final Touch", "A picture is worth a thousand words."]
        };

        let allBreeds = []; // Store breeds for the selected pet type

        async function fetchBreeds(petType) {
            try {
                const response = await fetch(`api/get_breeds.php?pet_type=${petType}`);
                const result = await response.json();
                if (result.success) {
                    allBreeds = result.data;
                }
            } catch (error) {
                console.error("Failed to fetch breeds:", error);
            }
        }

        function filterBreeds(query) {
            const suggestionsContainer = document.getElementById('breedSuggestions');
            if (!query) {
                renderSuggestions(allBreeds);
                return;
            }

            const filteredGroups = allBreeds.map(group => {
                return {
                    ...group,
                    breeds: group.breeds.filter(breed =>
                        breed.name.toLowerCase().includes(query.toLowerCase())
                    )
                };
            }).filter(group => group.breeds.length > 0);

            renderSuggestions(filteredGroups);
        }

        function renderSuggestions(groups) {
            const container = document.getElementById('breedSuggestions');
            container.innerHTML = '';

            if (groups.length === 0) {
                container.innerHTML = '<div class="no-results">No matches found. You can keep typing to add your own.</div>';
                container.style.display = 'block';
                return;
            }

            groups.forEach(group => {
                const label = document.createElement('div');
                label.className = 'breed-group-label';
                label.textContent = group.group_name;
                container.appendChild(label);

                group.breeds.forEach(breed => {
                    const item = document.createElement('div');
                    item.className = 'breed-item';
                    item.textContent = breed.name;
                    item.onclick = (e) => {
                        e.stopPropagation();
                        selectBreed(breed.name);
                    };
                    container.appendChild(item);
                });
            });

            container.style.display = 'block';
        }

        function selectBreed(name) {
            document.getElementById('breedInput').value = name;
            document.getElementById('breedSuggestions').style.display = 'none';
        }

        function showSuggestions() {
            if (allBreeds.length > 0) {
                renderSuggestions(allBreeds);
            }
        }

        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.breed-container')) {
                document.getElementById('breedSuggestions').style.display = 'none';
            }
        });

        function selectType(el, type) {
            document.querySelectorAll('.type-option').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('pet_type').value = type;

            // Clear previous breeds and fetch new ones
            allBreeds = [];
            document.getElementById('breedInput').value = '';
            fetchBreeds(type);

            // Auto advance for smoother UI
            setTimeout(() => changeStep(1), 300);
        }

        function selectGender(el, val) {
            document.querySelectorAll('.pill-option').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('gender_input').value = val;
        }

        function showPreview(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    document.querySelector('.upload-icon').style.display = 'none';
                    document.querySelector('.upload-zone p').textContent = 'Change Photo';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function changeStep(n) {
            // Simple validation before going next
            if (n === 1) {
                if (currentStep === 1 && !document.getElementById('pet_type').value) return alert("Please select a pet type.");
                if (currentStep === 2) {
                    const name = document.querySelector('input[name="pet_name"]').value;
                    const bday = document.querySelector('input[name="age"]').value;
                    if (!name || !bday) return alert("Please fill in the required fields.");
                }
            }

            // Hide current
            document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');

            // Update index
            currentStep += n;

            // Submit if finished
            if (currentStep > totalSteps) {
                form.submit();
                return;
            }

            // Show new
            document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');

            // Update Header
            document.getElementById('stepTitle').textContent = titles[currentStep][0];
            document.getElementById('stepSub').textContent = titles[currentStep][1];

            // Update Progress Dots
            document.querySelectorAll('.progress-dot').forEach((dot, idx) => {
                const stepNum = idx + 1;
                dot.classList.remove('active', 'completed');
                if (stepNum === currentStep) dot.classList.add('active');
                if (stepNum < currentStep) dot.classList.add('completed');
            });

            // Update Buttons
            document.getElementById('prevBtn').disabled = (currentStep === 1);
            const nextBtn = document.getElementById('nextBtn');
            if (currentStep === totalSteps) {
                nextBtn.innerHTML = 'Post Listing <i class="fa-solid fa-check"></i>';
            } else {
                nextBtn.innerHTML = 'Next <i class="fa-solid fa-arrow-right"></i>';
            }
        }
    </script>
</body>

</html>