<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $location = $_POST['location'];
    $bio = $_POST['bio'];

    try {
        $update = $pdo->prepare("UPDATE users SET full_name = ?, location = ?, bio = ? WHERE id = ?");
        $update->execute([$full_name, $location, $bio, $user_id]);
        $_SESSION['user_name'] = $full_name; // Sync session
        $success = "Profile updated successfully! ✨";

        // Refresh user data
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --gray-100: #f3f4f6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .profile-card {
            background: white;
            padding: 2.5rem;
            border-radius: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            border: 1px solid #f3f4f6;
        }

        .avatar-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid var(--primary);
            object-fit: cover;
        }

        h1 {
            font-family: 'Outfit';
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            outline: none;
            transition: 0.2s;
        }

        input:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .alert {
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>

<body>
    <div class="profile-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <a href="dashboard.php" style="color: var(--primary); text-decoration: none; font-size: 0.85rem;"><i
                    class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="avatar-wrap">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=3b82f6&color=fff&size=200"
                class="avatar-img">
        </div>

        <h1>Edit Profile</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                    required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location"
                    value="<?php echo htmlspecialchars($user['location'] ?? 'San Francisco, CA'); ?>">
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>

</html>