<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
include 'db.php';
include 'helpers.php';
require_admin();
// Every POST on this page (create, publish, delete, mark as read) must carry the CSRF token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); }

// Handle Deletion
if(isset($_POST['delete'])){
    $id = (int)$_POST['delete'];
    mysqli_query($conn, "DELETE FROM job_openings WHERE id = $id");
    header("Location: manage_jobs.php");
    exit;
}

// Handle Addition
if (isset($_POST['add_job'])) {
    $title    = trim($_POST['title'] ?? '');
    $loc      = trim($_POST['location'] ?? '');
    $type     = trim($_POST['job_type'] ?? '');
    $cat      = clean_choice($_POST['category'] ?? '', ['medical', 'social', 'admin'], 'admin');
    $uploader = $_SESSION['admin_name'];

    $stmt = mysqli_prepare($conn, "INSERT INTO job_openings (title, location, job_type, category, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $title, $loc, $type, $cat, $uploader);
    mysqli_stmt_execute($stmt);
    header("Location: manage_jobs.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Careers | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/manage_jobs.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-card">
            <h2><i class="fas fa-plus-circle"></i> Post a New Job</h2>
            <form method="POST">
                        <input type="hidden" name="t" value="<?php echo e(csrf_token()); ?>">
                <input type="text" name="title" placeholder="Job Title (e.g. Registered Nurse)" required>
                <input type="text" name="location" placeholder="Location (e.g. Karen, Nairobi)" required>
                <select name="job_type">
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Contract">Contract</option>
                </select>
                <select name="category">
                    <option value="medical">Medical</option>
                    <option value="social">Social Work</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="add_job" class="btn-save">Post Job Opening</button>
            </form>
        </div>

        <h3>Active Openings</h3>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM job_openings ORDER BY created_at DESC");
        while($row = mysqli_fetch_assoc($res)) {
            // Check if uploaded_by exists, otherwise default to Admin
            $admin_name = e(!empty($row['uploaded_by']) ? $row['uploaded_by'] : 'Admin');

            echo "<div class='job-row'>
                    <div>
                        <span class='posted-by'><i class='fas fa-user-edit'></i> Posted by: ".$admin_name."</span><br>
                        <strong style='font-size: 16px; color: #062269;'>".e($row['title'])."</strong><br>
                        <small style='color: #666;'>".e($row['location'])." | ".e($row['job_type'])." (Category: ".e($row['category']).")</small>
                    </div>
                    ".action_button(['delete' => $row['id']], "<i class='fas fa-trash'></i>", 'icon-action', 'color:#e74c3c; font-size: 18px;', 'Delete this job?')."
                  </div>";
        }
        ?>
    </main>
</body>
</html>