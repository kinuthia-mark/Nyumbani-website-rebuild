<?php 
// 1. Connect to the database
include 'admin/db.php'; 

// 2. Fetch the MOST RECENT post for the Featured Section
$featured_query = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 1");
$featured_post = mysqli_fetch_assoc($featured_query);

// 3. Fetch ALL posts for the grid
$grid_query = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News & Stories – Nyumbani Children's Home</title>
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="css/pages/blog.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="featured-post">
        <div class="blog-container">
            <div class="featured-content">
                <?php if ($featured_post): ?>
                    <span class="featured-badge">Latest Story</span>
                    <h1><?php echo htmlspecialchars($featured_post['title']); ?></h1>
                    <p><?php echo htmlspecialchars(substr($featured_post['content'], 0, 150)); ?>...</p>
                    <a href="view_post.php?id=<?php echo $featured_post['id']; ?>" class="btn-read" style="color:#fff; border-bottom: 2px solid #fff;">
                        Read Full Story <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <h1>Welcome to our Blog</h1>
                    <p>Check back soon for news and success stories.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="blog-container">
        
        <div class="blog-filters">
            <button class="filter-btn active" data-filter="all">All Posts</button>
            <button class="filter-btn" data-filter="success">Success Stories</button>
            <button class="filter-btn" data-filter="programs">Programs</button>
            <button class="filter-btn" data-filter="events">Events</button>
        </div>

        <div class="blog-grid" id="blog-grid">
            
            <?php 
            if (mysqli_num_rows($grid_query) > 0):
                while($post = mysqli_fetch_assoc($grid_query)): 
                    // Set image path (use a default if empty)
                    $img = !empty($post['image_path']) ? $post['image_path'] : 'images/blog_default.jpg';
                    // Category logic (optional: you can add a category column to your DB)
                    $category = isset($post['category']) ? $post['category'] : 'all';
            ?>
                <article class="blog-card" data-category="<?php echo htmlspecialchars($category); ?>">
                    <div class="blog-img" style="background-image: url('<?php echo htmlspecialchars($img, ENT_QUOTES); ?>');"></div>
                    <div class="blog-info">
                        <div class="blog-meta">
                            <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...</p>
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn-read">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php 
                endwhile; 
            else:
                echo "<p>No stories found.</p>";
            endif;
            ?>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Javascript filtering logic stays the same
        const filterButtons = document.querySelectorAll('.filter-btn');
        const blogCards = document.querySelectorAll('.blog-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const filterValue = button.getAttribute('data-filter');

                blogCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    if (filterValue === 'all' || category === filterValue) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>

</body>
</html>