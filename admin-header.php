<?php
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@petcloud.com';
?>
<header class="admin-header">
    <div class="header-left">
        <button class="menu-toggle-btn" onclick="if(window.toggleAdminSidebar) window.toggleAdminSidebar();">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" placeholder="Global search..." class="search-input">
        </div>
    </div>

    <div class="header-right">
        <!-- Notifications Removed -->

        <div class="user-profile">
            <div class="user-info-text">
                <div class="user-name">
                    <?php echo htmlspecialchars($adminName); ?>
                </div>
                <div class="user-role">System Administrator</div>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=10b981&color=fff&bold=true"
                alt="Admin Avatar" class="user-avatar-img">
        </div>
    </div>
</header>

<style>
    .admin-header {
        margin-left: 260px;
        height: 80px;
        background: white;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 2.5rem;
        position: sticky;
        top: 0;
        z-index: 900;
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

    .search-container {
        background: #f3f4f6;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }

    .search-icon {
        color: #9ca3af;
        margin-right: 0.5rem;
    }

    .search-input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 0.875rem;
        width: 250px;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-left: 2rem;
        border-left: 1px solid #f3f4f6;
    }

    .user-info-text {
        text-align: right;
    }

    .user-name {
        font-weight: 700;
        font-size: 0.9375rem;
        color: #111827;
    }

    .user-role {
        font-size: 0.75rem;
        color: #10b981;
        font-weight: 600;
        text-transform: uppercase;
    }

    .user-avatar-img {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 2px solid #f3f4f6;
    }

    /* Mobile Responsiveness */
    @media (max-width: 1024px) {
        .admin-header {
            margin-left: 0;
            padding: 0 1.25rem;
        }

        .menu-toggle-btn {
            display: block;
        }

        .search-input {
            width: 150px;
            /* Smaller search box on tablet/mobile */
        }
    }

    @media (max-width: 640px) {
        .user-info-text {
            display: none;
            /* Hide name/role on very small screens */
        }

        .user-profile {
            padding-left: 0;
            border-left: none;
        }

        .search-container {
            display: none;
            /* Optionally hide search on mobile if space is tight, or make it an icon */
        }
    }
</style>