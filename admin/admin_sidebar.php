<?php
// 1. Get the current file name to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Fetch the unread messages count from the database
$unread_count = 0;
if (isset($conn)) {
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE status = 'unread'");
    if ($count_res) {
        $count_data = mysqli_fetch_assoc($count_res);
        $unread_count = $count_data['total'];
    }
}

// 3. Get the logged-in user's name from the session
// This will display "David" or "Mark" based on your 'users' table session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';
?>

<link rel="stylesheet" href="../css/admin/sidebar.css">

<aside class="admin-sidebar">
    <div class="admin-nav-container">
        <h2>Nyumbani Admin</h2>

        <div class="user-profile-section">
            <div class="user-avatar">
                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($admin_name); ?></span>
                <span class="user-status">Online Now</span>
            </div>
        </div>
        
        <a href="manage_gallery.php" class="admin-nav-link <?php echo ($current_page == 'manage_gallery.php') ? 'active' : ''; ?>">
            <i class="fas fa-images"></i> Manage Gallery
        </a>
        
        <a href="manage_blog.php" class="admin-nav-link <?php echo ($current_page == 'manage_blog.php') ? 'active' : ''; ?>">
            <i class="fas fa-blog"></i> Blog Posts
        </a>

        <a href="manage_jobs.php" class="admin-nav-link <?php echo ($current_page == 'manage_jobs.php') ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i> Manage Careers
        </a>

        <div class="nav-label">Resource Archive</div>

        <a href="manage_newsletters.php" class="admin-nav-link <?php echo ($current_page == 'manage_newsletters.php') ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i> Newsletters
        </a>

        <a href="manage_annual_reports.php" class="admin-nav-link <?php echo ($current_page == 'manage_annual_reports.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Annual Reports
        </a>

        <a href="manage_audit_reports.php" class="admin-nav-link <?php echo ($current_page == 'manage_audit_reports.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i> Audit Reports
        </a>

        <a href="manage_messages.php" class="admin-nav-link <?php echo ($current_page == 'manage_messages.php') ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i> Messages
            <?php if($unread_count > 0): ?>
                <span class="msg-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="admin-sidebar-footer">
        <a href="../index.php" class="admin-nav-link" target="_blank">
            <i class="fas fa-globe"></i> Visit Website
        </a>
        <a href="logout.php" class="admin-nav-link" style="color: #ff7675 !important;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>