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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $discount = $_POST['discount'] ?: 0;
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    // Default image
    $img = 'https://images.unsplash.com/photo-1583512676605-934c25b412f8?w=600';

    // Handle File Upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "images/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_extension, $allowed_types)) {
            $new_filename = uniqid('prod_') . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $img = $target_file;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, discount, category, image_url, stock, status, shop_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $desc, $price, $discount, $category, $img, $stock, $status, $shop_id])) {
                header("Location: shop-products.php?msg=Product Added");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Product - PetCloud</title>
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

            .form-grid {
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

        .form-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2.5rem;
            max-width: 800px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            outline: none;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn {
            padding: 0.85rem 2rem;
            border-radius: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
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
            <a href="shop-products.php"
                style="color: #64748b; text-decoration: none; font-size: 0.9rem; margin-bottom: 1rem; display: block;"><i
                    class="fa-solid fa-arrow-left"></i> Back to Products</a>
            <h2>Add New Product</h2>
        </div>

        <div class="form-card">
            <?php if ($error): ?>
                <div style="color: red; margin-bottom: 1rem; padding: 1rem; background: #fee2e2; border-radius: 0.5rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="High-quality kibble">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Describe the health benefits, size, or usage..."></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="Food">Food & Nutrition</option>
                            <option value="Toys">Toys & Play</option>
                            <option value="Health">Supplements & Health</option>
                            <option value="Accessories">Bedding & Accessories</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active (Visible)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="29.99">
                    </div>
                    <div class="form-group">
                        <label>Discount (%)</label>
                        <input type="number" step="0.01" name="discount" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" required value="50">
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="product_image" class="form-control" accept="image/*">
                        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">Upload a high-quality image
                            (JPG, PNG, WEBP)</small>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Publish Product</button>
                    <a href="shop-products.php" class="btn"
                        style="flex: 1; text-align: center; background: #f1f5f9; color: #475569;">Discard</a>
                </div>
            </form>
        </div>
    </main>

</body>

</html>