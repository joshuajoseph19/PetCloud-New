<?php
// shop-header.php
?>
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle-btn" onclick="if(window.toggleShopSidebar) window.toggleShopSidebar();">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="shop-search" placeholder="Search orders, products or customers...">
        </div>
    </div>

    <div class="header-actions">
        <a href="shop-notifications.php" class="icon-btn notification-btn">
            <i class="fa-regular fa-bell"></i>
            <?php
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM shop_notifications WHERE shop_id = ? AND is_read = 0");
            $stmt->execute([$shop_id ?? 0]);
            $notifCount = $stmt->fetchColumn();
            if ($notifCount > 0): ?>
                <span class="notification-badge"><?php echo $notifCount; ?></span>
            <?php endif; ?>
        </a>

        <div class="user-profile">
            <div class="user-info-text">
                <div class="user-name-text">
                    <?php echo htmlspecialchars($user_name); ?>
                </div>
                <div class="user-shop-text">
                    <?php echo htmlspecialchars($shopName); ?>
                </div>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=4f46e5&color=fff"
                class="user-avatar-img">
        </div>
    </div>
</header>

<style>
    .top-header {
        height: 70px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 2rem;
        position: sticky;
        top: 0;
        z-index: 999;
        margin-left: 280px;
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .menu-toggle-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0.5rem;
    }

    .search-bar {
        display: flex;
        align-items: center;
        background: #f9fafb;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        width: 400px;
        border: 1px solid transparent;
        transition: 0.2s;
    }

    .search-bar:focus-within {
        background: #fff;
        border-color: #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .search-bar i {
        color: #9ca3af;
        margin-right: 0.75rem;
    }

    .search-bar input {
        border: none;
        background: transparent;
        outline: none;
        width: 100%;
        color: #1f2937;
        font-size: 0.95rem;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .icon-btn {
        position: relative;
        cursor: pointer;
        color: #4b5563;
        font-size: 1.25rem;
        text-decoration: none;
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
        border: 2px solid #fff;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-left: 1.5rem;
        border-left: 1px solid #f3f4f6;
    }

    .user-info-text {
        text-align: right;
    }

    .user-name-text {
        font-size: 0.9rem;
        font-weight: 600;
        color: #111827;
    }

    .user-shop-text {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .user-avatar-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    /* Mobile Responsiveness */
    @media (max-width: 1024px) {
        .top-header {
            margin-left: 0;
            padding: 0 1rem;
        }

        .menu-toggle-btn {
            display: block;
        }

        .search-bar {
            width: 200px;
        }
    }

    @media (max-width: 768px) {
        .search-bar {
            display: none;
            /* Hide search bar on mobile if necessary, or make it an icon */
        }

        .user-info-text {
            display: none;
        }

        .user-profile {
            padding-left: 0;
            border-left: none;
        }
    }
</style>