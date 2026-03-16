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

// Handle Delete
if (isset($_GET['delete'])) {
    $pid = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND shop_id = ?");
    if ($stmt->execute([$pid, $shop_id])) {
        $success = "Product deleted successfully.";
    }
}

// Handle Status Toggle
if (isset($_GET['toggle'])) {
    $pid = $_GET['toggle'];
    $current = $_GET['status'];
    $newStatus = ($current == 'active') ? 'inactive' : 'active';
    $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ? AND shop_id = ?");
    $stmt->execute([$newStatus, $pid, $shop_id]);
    header("Location: shop-products.php");
    exit();
}

// Fetch Products
$stmt = $pdo->prepare("SELECT * FROM products WHERE shop_id = ? ORDER BY created_at DESC");
$stmt->execute([$shop_id]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #4f46e5; --bg: #f8fafc; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); }
        .main-wrapper { margin-left: 280px; padding: 2.5rem; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0; padding: 1.5rem; }
        }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .page-title h2 { font-family: 'Outfit'; font-size: 1.75rem; }
        
        .btn { padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }

        .content-card { background: white; border-radius: 1.5rem; border: 1px solid #e5e7eb; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        .product-table { width: 100%; border-collapse: collapse; }
        .product-table th { text-align: left; padding: 1.25rem 1rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        .product-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .prod-info { display: flex; align-items: center; gap: 1rem; }
        .prod-img { width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover; }
        .prod-name { font-weight: 600; color: #111827; }
        .prod-cat { font-size: 0.8rem; color: #64748b; }
        
        .badge { padding: 0.35rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 700; }
        .badge-active { background: #ecfdf5; color: #047857; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }
        .badge-low { background: #fff1f2; color: #e11d48; }

        .action-btns { display: flex; gap: 0.5rem; }
        .icon-btn { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b; transition: 0.2s; text-decoration: none; border: 1px solid #e5e7eb; }
        .icon-btn:hover { background: #f9fafb; color: var(--primary); border-color: var(--primary); }
    </style>
</head>
<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h2>Products Marketplace</h2>
                <p style="color: #64748b;">Manage your inventory, pricing and visibility.</p>
            </div>
            <a href="shop-add-product.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add New Product
            </a>
        </div>

        <?php if ($success): ?>
            <div style="background: #ecfdf5; color: #047857; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="content-card">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 4rem; color: #94a3b8;">No products found. Start by adding one!</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div class="prod-info">
                                    <img src="<?php echo $p['image_url']; ?>" class="prod-img">
                                    <div>
                                        <div class="prod-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                        <div class="prod-cat">ID: #<?php echo $p['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span style="font-size: 0.9rem; font-weight: 500; color: #4b5563;"><?php echo htmlspecialchars($p['category']); ?></span></td>
                            <td>
                                <div style="font-weight: 700; color: #111827;">₹<?php echo number_format($p['price'], 2); ?></div>
                                <?php if ($p['discount'] > 0): ?>
                                    <div style="font-size: 0.75rem; color: #10b981;">-<?php echo $p['discount']; ?>% Off</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #111827;"><?php echo $p['stock']; ?></div>
                                <?php if ($p['stock'] < 10): ?>
                                    <span class="badge badge-low">Low Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="shop-products.php?toggle=<?php echo $p['id']; ?>&status=<?php echo $p['status']; ?>" style="text-decoration:none;">
                                    <span class="badge badge-<?php echo $p['status']; ?>">
                                        <?php echo ucfirst($p['status']); ?>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="shop-edit-product.php?id=<?php echo $p['id']; ?>" class="icon-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="shop-products.php?delete=<?php echo $p['id']; ?>" class="icon-btn" title="Delete" style="color: #ef4444;" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
