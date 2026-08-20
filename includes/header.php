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
        <nav style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <a href="index.php" style="font-size: 1.2rem;">✈️ TravelTales</a> | 
                <a href="index.php">Explore Stories</a>
            </div>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="editor.php" class="btn">+ New Story</a>
                    <a href="logout.php" style="color: #666; margin-left: 10px;">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a> | 
                    <a href="register.php">Join Travel Stories</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>