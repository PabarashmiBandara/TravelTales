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
    <!-- Lightweight CSS framework for fast styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php"><strong>✈️ TravelTales</strong></a> | 
            <a href="index.php">Explore Stories</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                | <a href="editor.php">New Story</a>
                | <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                | <a href="login.php">Login</a>
                | <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <hr>
    <main>