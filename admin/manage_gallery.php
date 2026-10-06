<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}
include 'db.php';
include 'helpers.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Gallery | Nyumbani Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/manage_gallery.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-card">
            <h2><i class="fas fa-camera" style="color: #4175FC;"></i> Add to Gallery</h2>
            <p style="color: #666; margin-bottom: 20px;">Upload new photos to the public gallery page.</p>
            
            <form action="gallery_handler.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="t" value="<?php echo e(csrf_token()); ?>">
                <div style="margin-bottom: 15px;">
                    <label>Image Caption</label>
                    <input type="text" name="caption" placeholder="e.g. Daily Life at Karen Home" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Select Image (JPG/PNG)</label><br>
                    <input type="file" name="gallery_img" accept="image/*" required style="margin-top: 10px;">
                </div>

                <button type="submit" name="submit_gallery" class="btn-upload">Upload Photo</button>
            </form>
        </div>

        <h3>Current Gallery Photos</h3>
        <div class="image-preview-grid">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM gallery ORDER BY upload_date DESC");
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    // Pulling the uploader name from DB
                    $uploaded_by = e(!empty($row['uploaded_by']) ? $row['uploaded_by'] : 'Admin');
                    
                    echo "<div class='img-item'>
                            <img src='../".$row['image_path']."'>
                            <span class='uploader-tag'><i class='fas fa-user'></i> ".$uploaded_by."</span>
                            <p style='font-weight:600; font-size:14px; margin: 5px 0;'>".e($row['caption'])."</p>
                            ".action_button(['id' => $row['id']], "<i class='fas fa-trash'></i> Delete", 'icon-action', 'color:#e74c3c; font-size:13px;', 'Are you sure you want to delete this photo?', 'delete_photo.php')."
                          </div>";
                }
            } else {
                echo "<p style='color: #888;'>No photos uploaded yet.</p>";
            }
            ?>
        </div>
    </main>

</body>
</html>