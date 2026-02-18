<?php
// Ensure $foundReportsCount is set if not already
if (!isset($foundReportsCount)) {
    // Only try to fetch if we have $pdo and $user_id
    if (isset($pdo) && isset($user_id)) {
        try {
            $reportsCountStmt = $pdo->prepare("
SELECT COUNT(*) FROM found_pet_reports fr
JOIN lost_pet_alerts lpa ON fr.alert_id = lpa.id
WHERE lpa.user_id = ? AND lpa.status = 'Active'
");
            $reportsCountStmt->execute([$user_id]);
            $foundReportsCount = $reportsCountStmt->fetchColumn();
        } catch (Exception $e) {
            $foundReportsCount = 0;
        }
    } else {
        $foundReportsCount = 0;
    }
}

// Ensure $upcomingCount is set for schedule badge
if (!isset($upcomingCount)) {
    if (isset($pdo) && isset($user_id)) {
        try {
            $apptCountStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND status != 'cancelled' AND
appointment_date >= CURDATE()");
            $apptCountStmt->execute([$user_id]);
            $upcomingCount = $apptCountStmt->fetchColumn();
        } catch (Exception $e) {
            $upcomingCount = 0;
        }
    } else {
        $upcomingCount = 0;
    }
}

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-brand"
        style="padding: 0.5rem 1.5rem 0; display: flex; align-items: flex-start; margin-bottom: 0;">
        <img src="images/logo.png" alt="PetCloud Logo" style="width: 180px; height: auto; object-fit: contain;">
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Overview
        </a>
        <a href="adoption.php" class="nav-item <?php echo $currentPage == 'adoption.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-heart"></i> Adoption
        </a>
        <a href="pet-rehoming.php" class="nav-item <?php echo $currentPage == 'pet-rehoming.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house-chimney-user"></i> Pet Rehoming
        </a>
        <a href="mypets.php" class="nav-item <?php echo $currentPage == 'mypets.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-paw"></i> My Pets
        </a>
        <a href="feeding-manager.php"
            class="nav-item <?php echo $currentPage == 'feeding-manager.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-utensils"></i> Feeding Schedule
        </a>
        <a href="smart-feeder.php" class="nav-item <?php echo $currentPage == 'smart-feeder.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-microchip"></i> Smart Feeder
        </a>
        <a href="my-orders.php" class="nav-item <?php echo $currentPage == 'my-orders.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bag-shopping"></i> My Orders
        </a>
        <a href="schedule.php" class="nav-item <?php echo $currentPage == 'schedule.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-calendar"></i> Schedule
            <?php if ($upcomingCount > 0): ?>
                <span class="nav-badge"><?php echo $upcomingCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="marketplace.php" class="nav-item <?php echo $currentPage == 'marketplace.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bag-shopping"></i> Marketplace
        </a>
        <a href="health-records.php"
            class="nav-item <?php echo $currentPage == 'health-records.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-notes-medical"></i> Health Records
        </a>
        <a href="lost-pet-reports.php"
            class="nav-item <?php echo $currentPage == 'lost-pet-reports.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bullhorn"></i> Lost Pet Reports
            <?php if ($foundReportsCount > 0): ?>
                <span class="nav-badge" style="background: #ef4444;">
                    <?php echo $foundReportsCount; ?>
                </span>
            <?php endif; ?>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
        <?php if (isset($user_name)): ?>
            <div class="user-mini-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random"
                    alt="Profile" class="mini-avatar">
                <div class="mini-info">
                    <span class="mini-name">
                        <?php echo htmlspecialchars($user_name); ?>
                    </span>
                    <span class="mini-role">Premium Member</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</aside>