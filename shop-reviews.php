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

// Handle Reply
if (isset($_POST['add_reply'])) {
    $rid = $_POST['review_id'];
    $reply = $_POST['reply_text'];
    $stmt = $pdo->prepare("UPDATE product_reviews SET reply = ? WHERE id = ?");
    $stmt->execute([$reply, $rid]);
    header("Location: shop-reviews.php?success=Reply Sent");
    exit();
}

// Fetch Reviews for this shop's products
$stmt = $pdo->prepare("SELECT r.*, u.full_name as reviewer_name, p.name as product_name, p.image_url as product_img
                      FROM product_reviews r
                      JOIN users u ON r.user_id = u.id
                      JOIN products p ON r.product_id = p.id
                      WHERE p.shop_id = ?
                      ORDER BY r.created_at DESC");
$stmt->execute([$shop_id]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$stmtAvg = $pdo->prepare("SELECT AVG(rating) FROM product_reviews r JOIN products p ON r.product_id = p.id WHERE p.shop_id = ?");
$stmtAvg->execute([$shop_id]);
$avgRating = round($stmtAvg->fetchColumn(), 1) ?: "0.0";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Reviews - PetCloud</title>
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
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Outfit';
            font-size: 1.75rem;
        }

        .rating-summary {
            background: white;
            padding: 2rem;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .avg-box {
            text-align: center;
            border-right: 1px solid #f1f5f9;
            padding-right: 2rem;
        }

        .avg-val {
            font-size: 3rem;
            font-weight: 800;
            color: #111827;
            font-family: 'Outfit';
            line-height: 1;
        }

        .stars {
            color: #f59e0b;
            margin-top: 0.5rem;
        }

        .review-card {
            background: white;
            border-radius: 1.25rem;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .review-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .reviewer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .rev-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #4b5563;
        }

        .product-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 1rem;
            background: #f8fafc;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            width: fit-content;
        }

        .product-meta img {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            object-fit: cover;
        }

        .review-text {
            color: #374151;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .reply-box {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            border-left: 4px solid var(--primary);
        }

        .reply-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .reply-text {
            font-size: 0.9rem;
            color: #4b5563;
        }

        .reply-form textarea {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-size: 0.9rem;
            outline: none;
            margin-bottom: 0.75rem;
            resize: none;
        }

        .btn-reply {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <div>
                <h2>Customer Reviews</h2>
                <p style="color: #64748b;">Monitor feedback and respond to customer experiences.</p>
            </div>
        </div>

        <div class="rating-summary">
            <div class="avg-box">
                <div class="avg-val">
                    <?php echo $avgRating; ?>
                </div>
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++)
                        echo $i <= $avgRating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; ?>
                </div>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem; font-weight: 600;">Overall Rating
                </div>
            </div>
            <div>
                <h4 style="margin-bottom: 0.5rem; font-family: 'Outfit';">Maintain Excellence</h4>
                <p style="font-size: 0.9rem; color: #64748b; max-width: 400px;">Positive reviews boost your visibility
                    in the marketplace. Aim for high-quality service and quick shipping!</p>
            </div>
        </div>

        <?php if (empty($reviews)): ?>
            <div
                style="background: white; border-radius: 1.5rem; padding: 5rem; text-align: center; border: 1px dashed #cbd5e1;">
                <i class="fa-regular fa-star" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                <p style="color: #64748b;">No reviews have been posted for your products yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-top">
                        <div class="reviewer">
                            <div class="rev-avatar">
                                <?php echo strtoupper(substr($rev['reviewer_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($rev['reviewer_name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">
                                    <?php echo date('M d, Y', strtotime($rev['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="stars" style="font-size: 0.8rem;">
                            <?php for ($i = 1; $i <= 5; $i++)
                                echo $i <= $rev['rating'] ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; ?>
                        </div>
                    </div>

                    <div class="product-meta">
                        <img src="<?php echo $rev['product_img']; ?>">
                        <span>Re:
                            <?php echo htmlspecialchars($rev['product_name']); ?>
                        </span>
                    </div>

                    <div class="review-text">
                        <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                    </div>

                    <?php if ($rev['reply']): ?>
                        <div class="reply-box">
                            <div class="reply-title">Your Response</div>
                            <div class="reply-text">
                                <?php echo htmlspecialchars($rev['reply']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="reply-form">
                            <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                            <textarea name="reply_text" rows="2"
                                placeholder="Write a professional reply to the customer..."></textarea>
                            <div style="text-align: right;">
                                <button type="submit" name="add_reply" class="btn-reply">Send Reply</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>

</html>