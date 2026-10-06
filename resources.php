<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resources – Nyumbani Children's Home – COGRI</title>
    
    <link rel='stylesheet' id='astra-theme-css-css' href='https://nyumbani.or.ke/wp-content/themes/astra/assets/css/minified/frontend.min.css?ver=3.9.1' media='all' />
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <?php include 'header.php';
     ?>

    <link rel="stylesheet" href="css/pages/resources.css">
</head>
<body>

    <header class="resources-hero">
        <div class="container">
            <h1>Our Resources</h1>
            <p>Access our latest publications, financial reports, and community impact stories to stay informed about Nyumbani’s mission.</p>
        </div>
    </header>

    <section class="resources-grid-section">
        <div class="container">
            <div class="resources-grid">
                
                <div class="resource-card">
                    <div class="card-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <h3>Newsletters</h3>
                    <p>Read our monthly updates featuring volunteer spotlights, program success stories, and upcoming events.</p>
                    <a href="newsletter.php" class="btn-resource">View Newsletters</a>
                </div>

                <div class="resource-card">
                    <div class="card-icon"><i class="fas fa-file-invoice"></i></div>
                    <h3>Annual Reports</h3>
                    <p>Download our annual reviews detailing our organizational growth and community reach over the past year.</p>
                    <a href="annual-report.php" class="btn-resource">View Reports</a>
                </div>

                <div class="resource-card">
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Audit Reports</h3>
                    <p>Transparency is our priority. View our independently audited financial statements and transparency reports.</p>
                    <a href="audit-report.php" class="btn-resource">View Audits</a>
                </div>

                <div class="resource-card">
                    <div class="card-icon"><i class="fas fa-blog"></i></div>
                    <h3>Our Blog</h3>
                    <p>Insights and stories directly from the field. Deep dives into the lives and futures of the children we support.</p>
                    <a href="blog.php" class="btn-resource">Read Blog</a>
                </div>

            </div>
        </div>
    </section>

    <div class="container">
        <section class="cta-section">
            <h2>Support Our Mission</h2>
            <p>Your contributions help us continue providing essential resources and care to children in need.</p>
            <a href="donate.php" class="btn-resource" style="background-color: var(--accent-blue); color: white;">Donate Now</a>
        </section>
    </div>

    <?php include 'footer.php'; 
    ?>

</body>
</html>