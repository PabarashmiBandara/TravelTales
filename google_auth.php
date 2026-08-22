<?php
//Google Authentication Handler
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
    $jwt = $_POST['credential'];
    
    // Split the JWT to get the payload
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        $_SESSION['flash_error'] = "Invalid Google authentication token.";
        header("Location: login.php");
        exit;
    }
    
    // Decode the base64url encoded payload
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    
    // Verify client ID
    $expectedClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?? '';

    
    if (!$payload || !isset($payload['email']) || ($expectedClientId && isset($payload['aud']) && $payload['aud'] !== $expectedClientId)) {
        $_SESSION['flash_error'] = "Authentication failed. Token verification error.";
        header("Location: login.php");
        exit;
    }
    
    $email = $payload['email'];
    // Google names are usually in 'name' or 'given_name'
    $name = $payload['name'] ?? explode('@', $email)[0];
    
    // Sanitize username (make it alphanumeric, max 50 chars)
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $name));
    if (strlen($username) > 50) $username = substr($username, 0, 50);
    
    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT `id`, `username`, `email`, `role` FROM `users` WHERE `email` = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['flash_success'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
        } else {
            // New user, register them
            // Ensure unique username
            $checkUser = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = :username");
            $checkUser->execute([':username' => $username]);
            if ($checkUser->fetch()) {
                // If username taken, append random string
                $username = substr($username, 0, 40) . '_' . rand(1000, 9999);
            }
            
            // Insert with a secure random password (they login with Google)
            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES (:username, :email, :password, 'user')");
            $insertStmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':password' => $randomPassword
            ]);
            
            // Get new user ID and log them in
            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            $_SESSION['flash_success'] = "Account created successfully with Google! Welcome to Travel Tales.";
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        header("Location: index.php");
        exit;
        
    } catch (PDOException $e) {
        error_log("Google Auth DB Error: " . $e->getMessage());
        $_SESSION['flash_error'] = "A database error occurred during Google Sign-In.";
        header("Location: login.php");
        exit;
    }
} else {
    // Invalid request method or missing credential
    header("Location: login.php");
    exit;
}