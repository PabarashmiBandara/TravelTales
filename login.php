<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Fetch user record
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Verify password against database hash
        if ($user && password_verify($password, $user['password'])) {
            // Save active login session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<h2>Login to TravelTales</h2>

<?php if ($error): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <p style="margin: 0;"><?= htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>

<form action="login.php" method="POST">
    <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<?php require_once 'includes/footer.php'; ?>