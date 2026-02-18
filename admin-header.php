<?php
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@petcloud.com';
?>
<header style="
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
">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="background: #f3f4f6; padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
            <i class="fa-solid fa-magnifying-glass" style="color: #9ca3af; margin-right: 0.5rem;"></i>
            <input type="text" placeholder="Global search..."
                style="border: none; background: transparent; outline: none; font-size: 0.875rem; width: 250px;">
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 2rem;">
        <!-- Notifications Removed -->

        <div style="display: flex; align-items: center; gap: 1rem; padding-left: 2rem; border-left: 1px solid #f3f4f6;">
            <div style="text-align: right;">
                <div style="font-weight: 700; font-size: 0.9375rem; color: #111827;">
                    <?php echo htmlspecialchars($adminName); ?>
                </div>
                <div style="font-size: 0.75rem; color: #10b981; font-weight: 600; text-transform: uppercase;">System
                    Administrator</div>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=10b981&color=fff&bold=true"
                alt="Admin Avatar" style="width: 44px; height: 44px; border-radius: 12px; border: 2px solid #f3f4f6;">
        </div>
    </div>
</header>