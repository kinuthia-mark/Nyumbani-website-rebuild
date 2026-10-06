<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Fetch Quick Stats
$count_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM newsletters"))['total'] ?? 0;
$count_annual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM annual_reports"))['total'] ?? 0;
$count_audit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_reports"))['total'] ?? 0;
$count_jobs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM job_openings"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nyumbani Admin | Dashboard</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/admin.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <section class="welcome-banner">
            <h1>Hello, <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></h1>
            <p>Welcome back! Here is a summary of Nyumbani's digital presence.</p>
            
            <div class="quick-actions">
                <a href="manage_newsletters.php" class="action-btn">
                    <i class="fas fa-plus"></i> New Newsletter
                </a>
                <a href="manage_jobs.php" class="action-btn">
                    <i class="fas fa-plus"></i> New Job Opening
                </a>
                <a href="manage_audit_reports.php" class="action-btn">
                    <i class="fas fa-plus"></i> New Audit Report
                </a>
                <a href="manage_annual_reports.php" class="action-btn">
                    <i class="fas fa-plus"></i> New Annual Report
                </a>
                <a href="manage_blog.php" class="action-btn">
                    <i class="fas fa-edit"></i> Write Blog Post
                </a>
            </div>
        </section>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-newspaper"></i>
                <h3><?php echo $count_news; ?></h3>
                <p>Newsletters</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo $count_annual; ?></h3>
                <p>Annual Reports</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3><?php echo $count_audit; ?></h3>
                <p>Audits</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-briefcase"></i>
                <h3><?php echo $count_jobs; ?></h3>
                <p>Open Careers</p>
            </div>
        </div>

        <div class="status-box">
            <h2>System Status</h2>
            <p><i class="fas fa-check-circle" style="color: #27ae60;"></i> Database Connected</p>
            <p><i class="fas fa-check-circle" style="color: #27ae60;"></i> File Upload System Active</p>
            <p><i class="fas fa-info-circle" style="color: #4175FC;"></i> All website documents are currently up to date.</p>
        </div>
    </main>

</body>
</html>