<?php
include 'admin/db.php'; // Adjust path if necessary

// 1. Get the ID and make sure it's a number to prevent SQL injection
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: blog.php");
    exit;
}

$post_id = (int)$_GET['id'];

// 2. Fetch the post (Only if it is published)
$query = "SELECT * FROM blog_posts WHERE id = $post_id AND status = 'published'";
$result = mysqli_query($conn, $query);
$post = mysqli_fetch_assoc($result);

// 3. Handle 404 - Post not found
if (!$post) {
    echo "<h2>Post not found or has been removed.</h2><a href='blog.php'>Return to Blog</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | Nyumbani Blog</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/pages/view_post.css">
</head>
<body>

    <article class="blog-container">
        <a href="blog.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to All Stories</a>

        <header>
            <div class="post-meta">
                <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?> 
                <?php if(!empty($post['author'])): ?>
                    <span style="margin: 0 10px;">|</span> <i class="far fa-user"></i> By <?php echo htmlspecialchars($post['author']); ?>
                <?php endif; ?>
            </div>
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        </header>

        <?php if (!empty($post['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Featured Image" class="feature-image">
        <?php endif; ?>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <footer class="blog-footer">
            <p>Thanks for reading! Share this story to help support Nyumbani's mission.</p>
            <div class="share-buttons">
                </div>
        </footer>
    </article>

</body>
</html>