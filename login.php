<?php
/**
 * Travel Tales - User Login
 *
 * Authenticates users using password_verify() against hashed database passwords
 * and initializes secure PHP sessions.
 */

require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    $_SESSION['flash_info'] = "You are already logged in as " . htmlspecialchars($_SESSION['username']) . ".";
    header("Location: index.php");
    exit;
}

$pageTitle = "Log In to Your Account";
$error = '';
$email = '';

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email address and password.";
    } else {
        try {
            // Find user by email using prepared statement
            $stmt = $pdo->prepare("SELECT `id`, `username`, `email`, `password`, `role` FROM `users` WHERE `email` = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // Verify password hash
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store user information in session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];

                $_SESSION['flash_success'] = "Welcome back, {$user['username']}! Ready to explore or write a new travel tale?";
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email or password. Please check your credentials and try again.";
            }
        } catch (PDOException $e) {
            error_log("Login DB Error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container container-form auth-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h2>Welcome Back 🌍</h2>
            <p>Log in to manage and share your travel tales.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️ <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Log In</button>
        </form>

        <div class="form-footer">
            Don't have an account yet? <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>