<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle Quantity Updates ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_qty'])) {
        $cart_id = $_POST['cart_id'];
        $qty = $_POST['quantity'];
        if ($qty > 0) {
            $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?")->execute([$qty, $cart_id, $user_id]);
        } else {
            $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$cart_id, $user_id]);
        }
    }
    if (isset($_POST['remove_item'])) {
        $cart_id = $_POST['cart_id'];
        $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$cart_id, $user_id]);
    }
}

// --- Fetch Cart Items ---
$sql = "SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.added_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll();

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .cart-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-img {
            width: 100px;
            height: 100px;
            border-radius: 1rem;
            object-fit: cover;
        }

        .item-details {
            flex-grow: 1;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f3f4f6;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            width: fit-content;
        }

        .qty-btn {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #4b5563;
        }

        .summary-card {
            background: #f9fafb;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }

        .checkout-btn:hover {
            background: #059669;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand"
                style="padding: 0.5rem 1.5rem 0; display: flex; align-items: flex-start; margin-bottom: 0;">
                <img src="images/logo.png" alt="PetCloud Logo" style="width: 180px; height: auto; object-fit: contain;">
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-table-cells-large"></i> Overview</a>
                <a href="adoption.php" class="nav-item"><i class="fa-solid fa-heart"></i> Adoption</a>
                <a href="mypets.php" class="nav-item"><i class="fa-solid fa-paw"></i> My Pets</a>
                <a href="smart-feeder.php" class="nav-item"><i class="fa-solid fa-microchip"></i> Smart Feeder</a>
                <a href="schedule.php" class="nav-item"><i class="fa-regular fa-calendar"></i> Schedule</a>
                <a href="marketplace.php" class="nav-item active"><i class="fa-solid fa-bag-shopping"></i>
                    Marketplace</a>
                <a href="health-records.php" class="nav-item"><i class="fa-solid fa-notes-medical"></i> Health
                    Records</a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="cart-container">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                    <a href="marketplace.php" style="color: #6b7280; text-decoration: none;"><i
                            class="fa-solid fa-arrow-left"></i> Back to Shop</a>
                    <h1 style="font-family: 'Outfit'; margin: 0;">Your Shopping Cart</h1>
                </div>

                <div class="cart-card">
                    <?php if (empty($cartItems)): ?>
                        <div style="text-align: center; padding: 4rem 0;">
                            <i class="fa-solid fa-cart-shopping"
                                style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1.5rem;"></i>
                            <h2>Your cart is empty</h2>
                            <p style="color: #6b7280; margin-bottom: 2rem;">Looks like you haven't added anything to your
                                cart yet.</p>
                            <a href="marketplace.php" class="btn btn-primary"
                                style="text-decoration: none; padding: 1rem 2rem;">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="item-img" alt="Product">
                                <div class="item-details">
                                    <h3 style="font-family: 'Outfit'; margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </h3>
                                    <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </p>
                                    <div style="display: flex; align-items: center; gap: 2rem;">
                                        <div class="qty-control">
                                            <form method="POST" style="display: flex; align-items: center; gap: 1rem;">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <button type="submit" name="update_qty" value="down" class="qty-btn"
                                                    onclick="this.form.quantity.value--"><i
                                                        class="fa-solid fa-minus"></i></button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                    style="width: 40px; text-align: center; border: none; background: transparent; font-weight: 700; font-size: 1.1rem;"
                                                    readonly>
                                                <button type="submit" name="update_qty" value="up" class="qty-btn"
                                                    onclick="this.form.quantity.value++"><i
                                                        class="fa-solid fa-plus"></i></button>
                                            </form>
                                        </div>
                                        <span style="font-weight: 700; color: #10b981; font-size: 1.1rem;">₹
                                            <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="remove_item"
                                        style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-card">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span style="color: #6b7280;">Subtotal</span>
                                <span style="font-weight: 700;">₹
                                    <?php echo number_format($total, 2); ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span style="color: #6b7280;">Shipping</span>
                                <span style="color: #10b981; font-weight: 600;">FREE</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; border-top: 2px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                                <span style="font-size: 1.25rem; font-weight: 800;">Total</span>
                                <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;">₹
                                    <?php echo number_format($total, 2); ?>
                                </span>
                            </div>
                            <button class="checkout-btn" onclick="window.location.href='checkout.php'">Proceed to
                                Checkout</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>