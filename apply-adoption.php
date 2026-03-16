<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success = false;
$error = "";

// --- Fetch Pet Details if listing_id is provided ---
$listing_id = $_GET['listing_id'] ?? ($_POST['listing_id'] ?? null);
if ($listing_id) {
    $stmt = $pdo->prepare("SELECT * FROM adoption_listings WHERE id = ?");
    $stmt->execute([$listing_id]);
    $pet = $stmt->fetch();
    if ($pet) {
        $pet_name = $pet['pet_name'];
        $pet_category = $pet['pet_type'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    $user_id = $_SESSION['user_id'];
    $listing_id = $_POST['listing_id'] ?: null;
    $pet_name = $_POST['pet_name'];
    $pet_category = $_POST['pet_category'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $reason = trim($_POST['reason']);
    $living = $_POST['living_situation'];
    $other_pets = isset($_POST['other_pets']) ? 1 : 0;

    // --- Server-Side Validation ---
    if (empty($full_name) || empty($email) || empty($phone) || empty($reason) || empty($living)) {
        $error = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^[+]?[0-9\-\s]{10,15}$/", $phone)) {
        $error = "Invalid phone number. Please enter at least 10 digits.";
    } else {
        try {
            $sql = "INSERT INTO adoption_applications (user_id, listing_id, pet_name, pet_category, applicant_name, applicant_email, applicant_phone, reason_for_adoption, living_situation, has_other_pets) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$user_id, $listing_id, $pet_name, $pet_category, $full_name, $email, $phone, $reason, $living, $other_pets])) {
                $success = true;
            } else {
                $error = "Failed to submit application. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption Application - PetCloud</title>
    <!-- Combined Scripts -->
    <script src="js/form-validation.js" defer></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background: #f3f4f6;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .pet-mini-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .pet-mini-header i {
            font-size: 2rem;
            color: #10b981;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn-submit {
            background: #10b981;
            color: white;
            border: none;
            padding: 1rem;
            width: 100%;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #059669;
        }

        .success-box {
            text-align: center;
            padding: 3rem;
        }

        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <?php if ($success): ?>
            <div class="success-box">
                <i class="fa-solid fa-circle-check success-icon"></i>
                <h2>Application Submitted!</h2>
                <p style="color: #6b7280; margin-top: 1rem;">Thank you for your interest in adopting <strong>
                        <?php echo htmlspecialchars($pet_name); ?>
                    </strong>. We will review your application and contact you soon.</p>
                <a href="adoption.php" class="btn btn-primary"
                    style="margin-top: 2rem; display: inline-block; padding: 0.75rem 2rem; text-decoration: none; background: #10b981; color: white; border-radius: 0.5rem;">Back
                    to Adoption</a>
            </div>
        <?php else: ?>
            <div class="pet-mini-header">
                <i class="fa-solid fa-file-signature"></i>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif;">Adoption Application</h2>
                    <p style="color: #6b7280;">Applying for: <strong>
                            <?php echo htmlspecialchars($pet_name); ?>
                        </strong></p>
                </div>
            </div>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">
                <input type="hidden" name="pet_name" value="<?php echo htmlspecialchars($pet_name); ?>">
                <input type="hidden" name="pet_category" value="<?php echo htmlspecialchars($pet_category); ?>">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required
                        value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required
                        value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" required placeholder="0987654321">
                </div>

                <div class="form-group">
                    <label>Living Situation</label>
                    <select name="living_situation" required>
                        <option value="">Select an option</option>
                        <option value="House">House</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Studio">Studio</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem;">
                    <input type="checkbox" name="other_pets" id="other_pets" style="width: auto;">
                    <label for="other_pets" style="margin-bottom: 0;">Do you have other pets?</label>
                </div>

                <div class="form-group">
                    <label>Why do you want to adopt this pet?</label>
                    <textarea name="reason" rows="4" required
                        placeholder="Tell us a bit about why you think you're a good fit..."></textarea>
                </div>

                <button type="submit" name="submit_application" class="btn-submit">Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>