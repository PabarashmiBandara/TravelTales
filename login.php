<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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

<div class="card" style="max-width: 450px; margin: 30px auto;">
    <h2 style="margin-top:0;">Login to TravelTales</h2>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>