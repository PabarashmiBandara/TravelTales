<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$stmt = $pdo->query("
    SELECT blogPost.*, user.username 
    FROM blogPost 
    JOIN user ON blogPost.user_id = user.id 
    ORDER BY created_at DESC
");
$posts = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Explore Travel Stories</h2>
</div>

<?php if (empty($posts)): ?>
    <div class="card" style="text-align: center; padding: 40px 20px;">
        <h3>No stories published yet 🌍</h3>
        <p style="color: var(--text-muted);">Be the first explorer to share your journey with the community!</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="editor.php" class="btn" style="margin-top: 10px;">Create First Story</a>
        <?php else: ?>
            <a href="register.php" class="btn" style="margin-top: 10px;">Join TravelTales</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="card">
            <h2 style="margin-top: 0; color: var(--text-color);"><?= htmlspecialchars($post['title']); ?></h2>
            <div class="story-meta">
                Written by <strong><?= htmlspecialchars($post['username']); ?></strong> • <?= date('F j, Y', strtotime($post['created_at'])); ?>
            </div>
            <p style="white-space: pre-line; font-size: 1rem;"><?= htmlspecialchars($post['content']); ?></p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                <div class="story-actions">
                    <a href="editor.php?id=<?= $post['id']; ?>" class="btn btn-secondary">Edit</a>
                    <a href="delete.php?id=<?= $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this story?');">Delete</a>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>