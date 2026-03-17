<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- AUTO-FIX: Create Table If Missing ---
try {
    $pdo->query("SELECT 1 FROM products LIMIT 1");
} catch (PDOException $e) {
    // Run the setup script logic if table is missing
    include 'setup_marketplace_db.php';
}

// --- Fetch Cart Count ---
$cartCount = 0;
try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cartCount = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
}

// --- Handle AJAX Add to Cart ---
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $product_id = $_POST['product_id'];

    // Check if item exists in cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?")->execute([$existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$user_id, $product_id]);
    }

    // Return updated count
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo $stmt->fetchColumn();
    exit;
}

// --- Fetch Products ---
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY id ASC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .product-card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .btn-filter {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            padding: 0.5rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-filter.active {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 700;
        }

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
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 10vh auto;
            border-radius: 1.5rem;
            padding: 2rem;
            position: relative;
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
                    <input type="text" id="productSearch" placeholder="Search for food, toys, or services...">
                </div>
                <div class="header-actions">
                    <button class="icon-btn" style="position: relative;" onclick="window.location.href='cart.php'">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span id="cart-count-badge" class="cart-badge"
                            style="<?php echo $cartCount > 0 ? '' : 'display:none;'; ?>">
                            <?php echo $cartCount; ?>
                        </span>
                    </button>
                </div>
            </header>

            <div class="content-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1 style="font-family: 'Outfit';">Marketplace</h1>
                    <div style="display: flex; gap: 0.5rem;" id="filter-buttons">
                        <button class="btn-filter active" data-category="all">All</button>
                        <button class="btn-filter" data-category="Food">Food</button>
                        <button class="btn-filter" data-category="Toys">Toys</button>
                        <button class="btn-filter" data-category="Health">Health</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2.5rem;"
                    id="products-grid">
                    <?php
                    // Ensure all products have high-quality, working images
                    $workingImages = [
                        'Bird Seed Mix' => 'images/bird_feed.webp',
                        'Chew Bone' => 'images/chew_bone.jpg',
                        'Pet Vitamin Supplements' => 'images/Pet Vitamin Supplements.webp',
                        'Comfort Pet Bed' => 'images/Comfort Pet Bed.webp',
                        'Interactive Cat Toy' => 'images/cat_toy.jpg',
                        'Premium Dog Food' => 'images/premium_dog_food.webp',
                        'Puppy Food' => 'images/puppy_food.avif'
                    ];
                    foreach ($products as $product):
                        $img = $workingImages[$product['name']] ?? $product['image_url'];
                        ?>
                        <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>"
                            data-name="<?php echo strtolower($product['name']); ?>">
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="Product" class="product-image">
                            <div class="product-info">
                                <h3 style="font-size: 1.1rem; font-family: 'Outfit'; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem;">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </p>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                    <span
                                        style="font-weight: 700; color: #10b981; font-size: 1.25rem;">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <button class="btn btn-primary" style="padding: 0.6rem 1.2rem; border-radius: 0.75rem;"
                                        onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fa-solid fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Toast -->
    <div id="toast"
        style="display: none; position: fixed; bottom: 2rem; right: 2rem; background: #111827; color: white; padding: 1rem 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 2000; animation: slideUp 0.3s ease-out;">
        Item added to cart! 🛒
    </div>

    <style>
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
    </style>

    <script>
        const productSearch = document.getElementById('productSearch');
        const filterBtns = document.querySelectorAll('.btn-filter');
        const productCards = document.querySelectorAll('.product-card');
        const cartBadge = document.getElementById('cart-count-badge');
        const toast = document.getElementById('toast');

        // --- Search Logic ---
        productSearch.addEventListener('input', filterProducts);

        // --- Filter Logic ---
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterProducts();
            });
        });

        function filterProducts() {
            const searchTerm = productSearch.value.toLowerCase();
            const activeCategory = document.querySelector('.btn-filter.active').dataset.category;

            productCards.forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = activeCategory === 'all' || category === activeCategory;

                card.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
            });
        }

        // --- Add to Cart AJAX ---
        function addToCart(productId) {
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);

            fetch('marketplace.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(count => {
                    cartBadge.textContent = count;
                    cartBadge.style.display = 'block';

                    // Show Toast
                    toast.style.display = 'block';
                    setTimeout(() => { toast.style.display = 'none'; }, 2000);
                });
        }
    </script>
</body>

</html>