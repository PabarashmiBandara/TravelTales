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
    <title>TravelTales</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="header-nav">
    <a href="index.php" class="brand-logo">🧭 TravelTales</a>
    <div class="nav-links">
        <a href="index.php">Explore Stories</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="editor.php" class="btn-primary">+ Write Story</a>
            <a href="logout.php" style="color: var(--text-muted);">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Log In</a>
            <a href="register.php" class="btn-primary">Join TravelTales</a>
        <?php endif; ?>
    </div>
</nav>