<?php
include 'config/database.php';
include 'includes/functions.php';

// Jika sudah login, redirect ke admin page
if (checkAdminAuth()) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (verifyAdminPassword($pdo, $username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin.php");
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
}

include 'includes/header.php';
?>

<div class="admin-login-form">
    <h2>Login Admin</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="admin-login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required value="admin">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required value="password">
        </div>
        
        <div class="form-actions">
            <a href="index.php" class="btn btn-danger">Batal</a>
            <button type="submit" class="btn btn-success">Login</button>
        </div>
    </form>
    
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
        <small><strong>Default Login:</strong><br>
        Username: <code>admin</code><br>
        Password: <code>password</code><br><br>
        <strong>Tip:</strong> Anda juga boleh tekan <kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>A</kbd> untuk akses pantas</small>
    </div>
</div>

<?php include 'includes/footer.php'; ?>