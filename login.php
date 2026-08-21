<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email address or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<div class="auth-container">
    <h2 style="text-align: center; margin-top: 0;">Welcome Back 🌍</h2>
    <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">
        Log in to manage and share your travel tales.
    </p>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Log In</button>
    </form>
    
    <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 20px;">
        Don't have an account yet? <a href="register.php" style="color: var(--primary);">Create Account</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>