<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) { $errors[] = "Username is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Valid email is required."; }
    if (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters."; }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email is already taken.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $insertStmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
            if ($insertStmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword])) {
                $success = "Registration successful! <a href='login.php'>Click here to login</a>.";
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div class="card" style="max-width: 480px; margin: 40px auto;">
    <h2 style="margin-top:0; text-align: center;">Join TravelTales</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 24px;">Create an account to start blogging.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="btn" style="width: 100%;">Create Account</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>