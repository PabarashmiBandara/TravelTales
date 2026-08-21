<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$posts = [];

try {
    $stmt = $pdo->query("
        SELECT blogPost.*, user.username 
        FROM blogPost 
        JOIN user ON blogPost.user_id = user.id 
        ORDER BY blogPost.created_at DESC
    ");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("
            SELECT blogpost.*, user.username 
            FROM blogpost 
            JOIN user ON blogpost.user_id = user.id 
            ORDER BY blogpost.created_at DESC
        ");
        $posts = $stmt->fetchAll();
    } catch (PDOException $ex) {
        $posts = [];
    }
}
?>

<section class="hero-banner">
    <span class="hero-tag">✦ FOOTPRINTS & STORIES</span>
    <h1 class="hero-title">Go beyond the destination,<br>Discover the journey</h1>
    <p class="hero-subtitle">Explore inspiring travel stories, unforgettable experiences, hidden places, and practical guides from travelers around the globe.</p>
    <div>
        <a href="#stories" class="btn-primary">Explore Stories</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn-outline" style="margin-left: 10px;">Join TravelTales</a>
        <?php endif; ?>
    </div>
</section>

<div class="main-container" id="stories">
    <h2 style="margin-bottom: 20px;">Latest Travel Stories</h2>

    <?php if (empty($posts)): ?>
        <div class="card" style="text-align: center; padding: 50px 20px;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🗺️</div>
            <h3>No travel stories published yet</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Be the very first traveler to publish an inspiring adventure on TravelTales!</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="editor.php" class="btn-primary" style="margin-top: 15px;">Publish First Story</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary" style="margin-top: 15px;">Join & Publish</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="card">
                <h3 style="margin-top: 0; color: var(--text-main); font-size: 1.4rem;"><?= htmlspecialchars($post['title']); ?></h3>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px;">
                    Written by <strong><?= htmlspecialchars($post['username']); ?></strong> • <?= date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
                <p style="white-space: pre-line; font-size: 0.95rem; color: #334155;"><?= htmlspecialchars($post['content']); ?></p>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    <div style="margin-top: 18px; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; gap: 10px;">
                        <a href="editor.php?id=<?= $post['id']; ?>" class="btn-outline" style="color: var(--text-main); border-color: var(--border-color);">Edit</a>
                        <a href="delete.php?id=<?= $post['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this story?');">Delete</a>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>