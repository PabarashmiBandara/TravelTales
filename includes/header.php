<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelTales - Share Your Journey</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="nav-container">
            <div>
                <a href="index.php" class="brand-logo">✈️ TravelTales</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Explore Stories</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="editor.php" class="btn">+ Write Story</a>
                    <a href="logout.php" style="color: var(--text-muted);">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn">Get Started</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>