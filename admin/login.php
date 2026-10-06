<?php
session_set_cookie_params(["httponly" => true, "samesite" => "Lax"]);
session_start();
// If already logged in, skip this page
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nyumbani Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Admin Login</h2>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">Invalid username or password.</div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="user" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="pass" required>
                </div>
                <button type="submit" name="login_submit" class="btn-login">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>