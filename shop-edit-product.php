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

if (!isset($_GET['id'])) {
    header("Location: shop-products.php");
    exit();
}

$pid = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND shop_id = ?");
$stmt->execute([$pid, $shop_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: shop-products.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $discount = $_POST['discount'] ?: 0;
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $img = $_POST['image_url'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount = ?, category = ?, image_url = ?, stock = ?, status = ? WHERE id = ? AND shop_id = ?");
        if ($stmt->execute([$name, $desc, $price, $discount, $category, $img, $stock, $status, $pid, $shop_id])) {
            $success = "Product updated successfully!";
            // Update local object
            $product['name'] = $name;
            $product['description'] = $desc;
            $product['price'] = $price;
            $product['discount'] = $discount;
            $product['category'] = $category;
            $product['image_url'] = $img;
            $product['stock'] = $stock;
            $product['status'] = $status;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product - PetCloud</title>
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
            <h2>Edit Product</h2>
        </div>

        <?php if ($success): ?>
            <div style="background: #ecfdf5; color: #047857; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control"
                        rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="Food" <?php echo $product['category'] == 'Food' ? 'selected' : ''; ?>>Food &
                                Nutrition</option>
                            <option value="Toys" <?php echo $product['category'] == 'Toys' ? 'selected' : ''; ?>>Toys &
                                Play
                            </option>
                            <option value="Health" <?php echo $product['category'] == 'Health' ? 'selected' : ''; ?>>
                                Supplements & Health</option>
                            <option value="Accessories" <?php echo $product['category'] == 'Accessories' ? 'selected' : ''; ?>>Bedding & Accessories</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active
                                (Visible)</option>
                            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Inactive (Hidden)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required
                            value="<?php echo $product['price']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Discount (%)</label>
                        <input type="number" step="0.01" name="discount" class="form-control"
                            value="<?php echo $product['discount']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" required
                            value="<?php echo $product['stock']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="image_url" class="form-control"
                            value="<?php echo htmlspecialchars($product['image_url']); ?>">
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Save Changes</button>
                    <a href="shop-products.php" class="btn"
                        style="flex: 1; text-align: center; background: #f1f5f9; color: #475569;">Cancel</a>
                </div>
            </form>
        </div>
    </main>

</body>

</html>