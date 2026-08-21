<?php
// register.php
require_once 'config/db.php';
require_once 'includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) { $errors[] = "Username is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Valid email address is required."; }
    if (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters long."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email is already taken.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $insertStmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
            if ($insertStmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword])) {
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div class="auth-container">
    <h2 style="text-align: center; margin-top: 0;">Join TravelTales ✈️</h2>
    <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">
        Create your account to start writing and sharing your travel stories.
    </p>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="e.g. wanderer_sam" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Create Account</button>
    </form>

    <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 20px;">
        Already have an account? <a href="login.php" style="color: var(--primary);">Log In Here</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>