<?php
// User Registration

require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    $_SESSION['flash_info'] = "You are already logged in.";
    header("Location: index.php");
    exit;
}

$pageTitle = "Create an Account";
$error = '';
$username = '';
$email = '';

// Process Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation Checks
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required. Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = "Username must be between 3 and 30 characters and contain only letters, numbers, and underscores.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match. Please verify your password.";
    } else {
        try {
            // Check if username or email already exists using prepared statement
            $stmt = $pdo->prepare("SELECT `id`, `username`, `email` FROM `users` WHERE `username` = :username OR `email` = :email LIMIT 1");
            $stmt->execute([':username' => $username, ':email' => $email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                if (strtolower($existingUser['username']) === strtolower($username)) {
                    $error = "The username '{$username}' is already taken. Please choose another one.";
                } else {
                    $error = "An account with email '{$email}' already exists. Please log in.";
                }
            } else {
                // Securely hash the password before storing
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user
                $insertStmt = $pdo->prepare("INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES (:username, :email, :password, 'user')");
                $insertStmt->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $passwordHash
                ]);

                // Set success message and redirect to login page
                $_SESSION['flash_success'] = "Account created successfully! You can now log in to start sharing your travel tales.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Registration DB Error: " . $e->getMessage());
            $error = "An error occurred while creating your account. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container container-form auth-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h2>Join Travel Tales ✈️</h2>
            <p>Create your account to start writing and sharing your travel stories.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️ <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm" novalidate>
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?php echo htmlspecialchars($username); ?>" 
                       placeholder="e.g. Anne_Journey" required autofocus>
                <div class="form-help">Letters, numbers, and underscores only (3-30 characters).</div>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Re-enter password" required>
                <div id="passwordMatchError" class="form-help" style="color: #ef4444; display: none;"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Account</button>
        </form>

        <div class="form-footer">
            <!-- Google Sign-In -->
            <?php if (!empty($env['GOOGLE_CLIENT_ID']) && $env['GOOGLE_CLIENT_ID'] !== 'YOUR_GOOGLE_CLIENT_ID_HERE'): ?>
                <div style="margin: 0 0 24px 0; text-align: center; position: relative;">
                    <hr style="border: 0; border-top: 1px solid var(--border-color);">
                    <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--bg-card); padding: 0 12px; color: var(--text-muted); font-size: 0.85rem;">OR</span>
                </div>
                
                <script src="https://accounts.google.com/gsi/client" async defer></script>
                <div id="g_id_onload"
                     data-client_id="<?php echo htmlspecialchars($env['GOOGLE_CLIENT_ID']); ?>"
                     data-login_uri="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google_auth.php'; ?>"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="sign_in_with"
                     data-shape="rectangular"
                     data-logo_alignment="left"
                     style="display: flex; justify-content: center; margin-bottom: 24px;">
                </div>
            <?php endif; ?>

            Already have an account? <a href="login.php">Log In Here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>