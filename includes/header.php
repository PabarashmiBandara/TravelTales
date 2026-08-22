<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $_SESSION['user_id'] ?? null;
$currentUsername = $_SESSION['username'] ?? '';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
$flashInfo    = $_SESSION['flash_info'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);

$currentPage = basename($_SERVER['PHP_SELF']);
$currentFilter = $_GET['filter'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Travel Tales' : 'Travel Tales - Stories, journeys, and memories from around the world'; ?></title>
    <meta name="description" content="Discover inspiring travel stories, itineraries, and memories shared by travelers worldwide.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.svg">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="site-header">
        <div class="container nav-container">
            <a href="index.php" class="brand-logo" title="Travel Tales Home">
                <span class="logo-icon">🧭</span>
                <span>Travel <span class="highlight">Tales</span></span>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                ☰
            </button>

            <!-- Navigation Links -->
            <nav>
                <ul class="nav-links" id="navLinks">
                    <li class="nav-item <?php echo ($currentPage === 'index.php' && empty($currentFilter)) ? 'active' : ''; ?>">
                        <a href="index.php">Explore Stories</a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item <?php echo ($currentPage === 'index.php' && $currentFilter === 'my') ? 'active' : ''; ?>">
                            <a href="index.php?filter=my">My Stories</a>
                        </li>
                        <li class="nav-item <?php echo ($currentPage === 'editor.php') ? 'active' : ''; ?>">
                            <a href="editor.php" class="btn btn-primary btn-sm">✍️ Create Story</a>
                        </li>
                        <li class="nav-item">
                            <span class="user-badge" title="Logged in as <?php echo htmlspecialchars($currentUsername); ?>">
                                <span class="user-avatar"><?php echo strtoupper(substr($currentUsername, 0, 1)); ?></span>
                                <span><?php echo htmlspecialchars($currentUsername); ?></span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item <?php echo ($currentPage === 'login.php') ? 'active' : ''; ?>">
                            <a href="login.php">Log In</a>
                        </li>
                        <li class="nav-item <?php echo ($currentPage === 'register.php') ? 'active' : ''; ?>">
                            <a href="register.php" class="btn btn-primary btn-sm">Join Travel Tales</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Flash Alerts Container -->
    <div class="container flash-alerts">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert">
                <span>✅ <?php echo htmlspecialchars($flashSuccess); ?></span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-error" role="alert">
                <span>⚠️ <?php echo htmlspecialchars($flashError); ?></span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($flashInfo): ?>
            <div class="alert alert-info" role="alert">
                <span>ℹ️ <?php echo htmlspecialchars($flashInfo); ?></span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>
    </div>

    <main>