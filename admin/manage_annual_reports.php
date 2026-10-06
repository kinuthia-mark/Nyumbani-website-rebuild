<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
include 'db.php';
include 'helpers.php';
require_admin();
// Every POST on this page (create, publish, delete, mark as read) must carry the CSRF token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); }

// --- HANDLE UPLOAD ---
if (isset($_POST['save_report'])) {
    $title    = trim($_POST['doc_title'] ?? '');
    $year     = (int)($_POST['report_year'] ?? 0);
    $desc     = trim($_POST['doc_description'] ?? '');
    $status   = clean_choice($_POST['status'] ?? '', ['draft', 'published'], 'draft');
    $uploader = $_SESSION['admin_name'];

    $db_path = safe_upload($_FILES['pdf_file'] ?? [], 'reports', PDF_TYPES);
    if ($db_path) {
        $stmt = mysqli_prepare($conn, "INSERT INTO annual_reports (title, report_year, description, pdf_path, status, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sissss', $title, $year, $desc, $db_path, $status, $uploader);
        mysqli_stmt_execute($stmt);
        header("Location: manage_annual_reports.php?success=1"); exit;
    } else {
        header("Location: manage_annual_reports.php?error=upload"); exit;
    }
    exit;
}

// --- QUICK PUBLISH ---
if (isset($_POST['publish_id'])) {
    $id = (int)$_POST['publish_id'];
    mysqli_query($conn, "UPDATE annual_reports SET status = 'published' WHERE id = $id");
    header("Location: manage_annual_reports.php?updated=1"); exit;
}

// --- HANDLE DELETE ---
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $res = mysqli_query($conn, "SELECT pdf_path FROM annual_reports WHERE id = $id");
    $file = mysqli_fetch_assoc($res);
    if ($file) delete_upload($file['pdf_path']);
    mysqli_query($conn, "DELETE FROM annual_reports WHERE id = $id");
    header("Location: manage_annual_reports.php?deleted=1");
    exit;
}

$all_reports = mysqli_query($conn, "SELECT * FROM annual_reports ORDER BY report_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Annual Reports | Nyumbani Admin</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/manage_annual_reports.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main">
        <div class="management-layout">
            <div class="form-side">
                <div class="admin-card">
                    <h2 style="margin-top:0;"><i class="fas fa-file-pdf" style="color:#e74c3c;"></i> Post Annual Report</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="t" value="<?php echo e(csrf_token()); ?>">
                        <input type="hidden" name="status" id="postStatus" value="draft">
                        
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div style="grid-column: span 2;">
                                <label style="display:block; margin-bottom:0px; font-weight:600;">Report Title</label>
                                <input type="text" name="doc_title" id="inTitle" placeholder="e.g. 2024 Impact Report" required oninput="updatePreview()">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:0px; font-weight:600;">Fiscal Year</label>
                                <input type="number" name="report_year" id="inYear" value="2025" required oninput="updatePreview()">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:0px; font-weight:600;">PDF File</label>
                                <input type="file" name="pdf_file" accept=".pdf" required>
                            </div>
                            <div style="grid-column: span 2;">
                                <label style="display:block; margin-bottom:0px; font-weight:600;">Summary Description</label>
                                <textarea name="doc_description" id="inDesc" rows="3" placeholder="Briefly describe the highlights..." oninput="updatePreview()"></textarea>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" name="save_report" class="btn-draft" onclick="setStatus('draft')">Save Draft</button>
                            <button type="submit" name="save_report" class="btn-publish" onclick="setStatus('published')">Publish Live</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="preview-side">
                <div class="preview-sticky">
                    <div class="preview-box">
                        <div class="preview-icon-box">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="preview-body">
                            <h3 id="preTitle" style="margin:0; color:#062269;">Title Preview</h3>
                            <p id="preYear" style="color:#27ae60; font-weight:bold; margin: 10px 0;">Year: 2025</p>
                            <p id="preDesc" style="font-size:14px; color:#666;">Description summary will appear here...</p>
                        </div>
                    </div>
                    <p style="text-align:center; font-size:12px; color:#999; margin-top:15px;">Logged in as: <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                </div>
            </div>
        </div>

        <h3 class="section-title"><i class="fas fa-clock" style="color:#f39c12;"></i> Pending Drafts</h3>
        <div class="report-grid">
            <?php 
            mysqli_data_seek($all_reports, 0);
            $draft_count = 0;
            while($row = mysqli_fetch_assoc($all_reports)): 
                if($row['status'] == 'draft'): $draft_count++; ?>
                <div class="report-card">
                    <span class="status-tag st-draft">Draft</span>
                    <span class="uploader-info"><i class="fas fa-user-edit"></i> By: <?php echo htmlspecialchars($row['uploaded_by'] ?? 'Admin'); ?></span>
                    <h4 style="margin:0; color:#062269;"><?php echo e($row['title']); ?></h4>
                    <small style="color:#999;">Year: <?php echo $row['report_year']; ?></small>
                    <div class="card-actions">
                        <?php echo action_button(['publish_id' => $row['id']], '<i class="fas fa-paper-plane"></i> Publish', 'action-link', 'color:#27ae60;', ''); ?>
                        <?php echo action_button(['delete' => $row['id']], '<i class="fas fa-trash"></i> Delete', 'action-link', 'color:#e74c3c;', 'Delete permanently?'); ?>
                    </div>
                </div>
            <?php endif; endwhile; ?>
            <?php if($draft_count == 0) echo "<p style='color:#999;'>No drafts found.</p>"; ?>
        </div>

        <h3 class="section-title"><i class="fas fa-check-circle" style="color:#27ae60;"></i> Published History</h3>
        <div class="report-grid">
            <?php 
            mysqli_data_seek($all_reports, 0);
            $pub_count = 0;
            while($row = mysqli_fetch_assoc($all_reports)): 
                if($row['status'] == 'published'): $pub_count++; ?>
                <div class="report-card">
                    <span class="status-tag st-published">Live</span>
                    <span class="uploader-info"><i class="fas fa-user"></i> Uploaded by: <?php echo htmlspecialchars($row['uploaded_by'] ?? 'Admin'); ?></span>
                    <h4 style="margin:0; color:#062269;"><?php echo e($row['title']); ?></h4>
                    <small style="color:#999;">Year: <?php echo $row['report_year']; ?></small>
                    <div class="card-actions">
                        <a href="../<?php echo $row['pdf_path']; ?>" target="_blank" class="action-link" style="color:#4175FC;"><i class="fas fa-external-link-alt"></i> View PDF</a>
                        <?php echo action_button(['delete' => $row['id']], '<i class="fas fa-trash"></i> Delete', 'action-link', 'color:#e74c3c;', 'Retract and delete?'); ?>
                    </div>
                </div>
            <?php endif; endwhile; ?>
            <?php if($pub_count == 0) echo "<p style='color:#999;'>No published reports yet.</p>"; ?>
        </div>
    </main>

    <script>
        function setStatus(val) { document.getElementById('postStatus').value = val; }
        
        function updatePreview() {
            document.getElementById('preTitle').innerText = document.getElementById('inTitle').value || "Title Preview";
            document.getElementById('preYear').innerText = "Year: " + (document.getElementById('inYear').value || "2025");
            document.getElementById('preDesc').innerText = document.getElementById('inDesc').value || "Description summary will appear here...";
        }
    </script>
</body>
</html>