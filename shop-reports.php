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

// 1. Monthly Revenue Data (Last 6 Months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(o.created_at, '%b %Y') as month, 
                             SUM(oi.price_at_purchase * oi.quantity) as revenue,
                             DATE_FORMAT(o.created_at, '%Y-%m') as sort_month
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN products p ON oi.product_id = p.id
                      WHERE p.shop_id = ? AND o.status != 'Cancelled'
                      GROUP BY month, sort_month
                      ORDER BY sort_month ASC
                      LIMIT 6");
$stmt->execute([$shop_id]);
$revenueData = $stmt->fetchAll();

// 2. Sales by Category
$stmt = $pdo->prepare("SELECT p.category, SUM(oi.price_at_purchase * oi.quantity) as total
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE p.shop_id = ?
                      GROUP BY p.category");
$stmt->execute([$shop_id]);
$categoryData = $stmt->fetchAll();

// 3. Overall Totals
$totalSales = 0;
foreach ($revenueData as $r)
    $totalSales += $r['revenue'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reports & Insights - PetCloud</title>
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

            .report-grid {
                grid-template-columns: 1fr;
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

        .report-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .report-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2rem;
            position: relative;
        }

        .card-title {
            font-family: 'Outfit';
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 2rem;
            color: #1e293b;
        }

        .data-list {
            list-style: none;
        }

        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-item:last-child {
            border: none;
        }

        .data-label {
            font-weight: 600;
            color: #475569;
        }

        .data-value {
            font-family: 'Outfit';
            font-weight: 700;
            color: #111827;
        }

        .chart-placeholder {
            height: 200px;
            background: #f8fafc;
            border-radius: 1rem;
            border: 2px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }

        .btn-download {
            background: #111827;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <div>
                <h2>Performance Reports</h2>
                <p style="color: #64748b;">Analyze your sales growth and category performance.</p>
            </div>
            <a href="#" class="btn-download" onclick="alert('Preparing CSV export...')">
                <i class="fa-solid fa-file-export"></i> Download CSV Report
            </a>
        </div>

        <div class="report-grid">
            <div class="report-card">
                <div class="card-title">Revenue Growth (Monthly)</div>
                <div class="chart-placeholder">
                    <i class="fa-solid fa-chart-area" style="font-size: 2.5rem; margin-bottom: 1rem;"></i>
                    <p>Visual chart data loading...</p>
                </div>
                <ul class="data-list">
                    <?php if (empty($revenueData)): ?>
                        <li class="data-item">No sales data available for this range.</li>
                    <?php else: ?>
                        <?php foreach ($revenueData as $rd): ?>
                            <li class="data-item">
                                <span class="data-label">
                                    <?php echo $rd['month']; ?>
                                </span>
                                <span class="data-value">₹
                                    <?php echo number_format($rd['revenue'], 2); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="report-card">
                    <div class="card-title">Sales by Category</div>
                    <ul class="data-list">
                        <?php if (empty($categoryData)): ?>
                            <li class="data-item">No categories listed.</li>
                        <?php else: ?>
                            <?php foreach ($categoryData as $cd): ?>
                                <li class="data-item">
                                    <span class="data-label">
                                        <?php echo $cd['category']; ?>
                                    </span>
                                    <span class="data-value">₹
                                        <?php echo number_format($cd['total'], 2); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="report-card" style="background: var(--primary); color: white;">
                    <div class="card-title" style="color: white; margin-bottom: 1rem;">Quick Snapshot</div>
                    <div style="font-size: 2.5rem; font-weight: 800; font-family: 'Outfit';">₹
                        <?php echo number_format($totalSales, 2); ?>
                    </div>
                    <p style="opacity: 0.8; font-size: 0.85rem;">Gross sales in the last 6 months</p>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-circle-info"></i> Your top performing month was <strong>
                            <?php echo $revenueData[0]['month'] ?? 'N/A'; ?>
                        </strong>.
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>