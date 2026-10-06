<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
include 'db.php';
include 'helpers.php';
require_admin();
// Every POST on this page (create, publish, delete, mark as read) must carry the CSRF token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); }

// --- HANDLE ACTIONS ---
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    mysqli_query($conn, "DELETE FROM messages WHERE id = $id");
    header("Location: manage_messages.php"); exit;
}

if (isset($_POST['read'])) {
    $id = (int)$_POST['read'];
    mysqli_query($conn, "UPDATE messages SET status = 'read' WHERE id = $id");
    header("Location: manage_messages.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Message Center | Nyumbani Admin</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/manage_messages.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main">
        <h2 style="color: #062269;"><i class="fas fa-envelope-open-text"></i> Visitor Inquiries</h2>
        <p>Manage messages sent from the Contact Us page.</p>

        <?php
        $res = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");
        if (mysqli_num_rows($res) > 0):
            while($msg = mysqli_fetch_assoc($res)): ?>
                <div class="message-card <?php echo $msg['status']; ?>">
                    <div class="msg-header">
                        <div>
                            <span class="badge badge-<?php echo $msg['status']; ?>"><?php echo $msg['status']; ?></span>
                            <h3 style="margin: 5px 0;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                            <div class="msg-meta">
                                <strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> 
                                (<a href="mailto:<?php echo e($msg['email']); ?>"><?php echo e($msg['email']); ?></a>)
                            </div>
                        </div>
                        <div class="msg-meta"><?php echo date('M d, Y - h:i A', strtotime($msg['created_at'])); ?></div>
                    </div>
                    
                    <div class="msg-body">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>

                    <div class="actions">
                        <?php if($msg['status'] == 'unread'): ?>
                            <?php echo action_button(['read' => $msg['id']], 'Mark as Read', 'btn-read btn-sm', '', ''); ?>
                        <?php endif; ?>
                        <?php echo action_button(['delete' => $msg['id']], 'Delete', 'btn-delete btn-sm', '', 'Delete this message?'); ?>
                    </div>
                </div>
            <?php endwhile; 
        else: ?>
            <div class="admin-card" style="text-align: center; color: #666;">
                <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 10px;"></i>
                <p>Your inbox is empty.</p>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>