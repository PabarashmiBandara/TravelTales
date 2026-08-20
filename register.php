<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Form Validation
    if (empty($username)) { $errors[] = "Username is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Valid email address is required."; }
    if (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters long."; }

    if (empty($errors)) {
        // Check if username or email is already taken
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email is already in use.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user into database
            $insertStmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
            if ($insertStmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword])) {
                $success = "Account created successfully! <a href='login.php'>Click here to login</a>.";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<h2>Join TravelTales</h2>
<p>Create an account to start sharing your travel adventures.</p>

<?php if (!empty($errors)): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green; background: #e6ffe6; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <p style="margin: 0;"><?= $success; ?></p>
    </div>
<?php endif; ?>

<form action="register.php" method="POST">
    <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>
    <div>
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Sign Up</button>
</form>

<?php require_once 'includes/footer.php'; ?>