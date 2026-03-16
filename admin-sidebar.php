<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <img src="images/logo.png" alt="PetCloud Logo">
        <button class="close-sidebar-btn" id="closeSidebarBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="admin-dashboard.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge-high"></i> Overview
        </a>
        <a href="admin-users.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i> Users Management
        </a>
        <a href="admin-shop-approvals.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-shop-approvals.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-store"></i> Shop Approvals
        </a>
        <a href="admin-shops.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-shops.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-shop"></i> Managed Shops
        </a>
        <a href="admin-adoptions.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-adoptions.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-heart"></i> Adoptions
        </a>
        <a href="admin-adoption-approvals.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-adoption-approvals.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-check-double"></i> Listing Approvals
        </a>
        <a href="admin-platform-orders.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-platform-orders.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-invoice"></i> Platform Revenue
        </a>
        <a href="admin-notifications.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-notifications.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bullhorn"></i> Announcements
        </a>
        <a href="admin-settings.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-settings.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gears"></i> System Settings
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="admin-logout.php" class="nav-item logout-link">
            <i class="fa-solid fa-right-from-bracket"></i> Sign Out
        </a>
    </div>
</aside>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    /* Sidebar Base Styles */
    .admin-sidebar {
        width: 260px;
        background: white;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        color: #64748b;
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        border-right: 1px solid #e5e7eb;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        padding: 1rem;
        position: relative;
    }

    .sidebar-brand img {
        height: 60px;
        width: auto;
        object-fit: contain;
    }

    .close-sidebar-btn {
        display: none;
        position: absolute;
        top: 0.5rem;
        right: 0;
        background: none;
        border: none;
        color: #64748b;
        font-size: 1.5rem;
        cursor: pointer;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex: 1;
        overflow-y: auto;
        /* Allow scrolling if menu is tall */
    }

    .sidebar-footer {
        border-top: 1px solid #e5e7eb;
        padding-top: 1.5rem;
        margin-top: auto;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.25rem;
        color: #64748b;
        text-decoration: none;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .nav-item i {
        width: 20px;
        text-align: center;
    }

    .nav-item:hover {
        background-color: rgba(59, 130, 246, 0.05);
        color: #3b82f6;
        /* Primary Blue from user dashboard */
        transform: translateX(5px);
    }

    .nav-item.active {
        background-color: #3b82f6;
        /* Primary Blue */
        color: white;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }

    .logout-link {
        color: #f87171;
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Mobile Responsiveness */
    @media (max-width: 1024px) {
        .admin-sidebar {
            transform: translateX(-100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .admin-sidebar.open {
            transform: translateX(0);
        }

        .close-sidebar-btn {
            display: block;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeBtn = document.getElementById('closeSidebarBtn');

        // Close sidebar function
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }

        // Event listeners
        if (closeBtn) {
            closeBtn.addEventListener('click', closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Expose toggle function globally so header can use it
        window.toggleAdminSidebar = function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        };
    });
</script>