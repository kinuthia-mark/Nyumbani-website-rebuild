<?php 
include 'admin/db.php'; 
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Reports – Nyumbani Children's Home – COGRI</title>
    
    <link rel='stylesheet' href='css/style.css' media='all' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="css/pages/audit-report.css">
</head>
<body>

    <header class="audit-hero">
        <div class="container">
            <h1>Financial Audit Reports</h1>
            <p>We are committed to full financial transparency. Access our independently verified audit reports below.</p>
        </div>
    </header>

    <div class="container">
        <section class="audit-grid">
            
            <?php
            // Fetch reports from the table we created earlier
            $query = "SELECT * FROM audit_reports ORDER BY report_year DESC";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="audit-card">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3><?php echo $row['report_year']; ?> Audit Report</h3>
                        <p><?php echo !empty($row['description']) ? htmlspecialchars($row['description']) : "Full financial disclosure for the " . $row['report_year'] . " fiscal period."; ?></p>
                        
                        <a href="<?php echo htmlspecialchars($row['pdf_path']); ?>" class="btn-view-audit" target="_blank">View Audit</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center;'>No audit reports available at this time.</p>";
            }
            ?>

        </section>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>