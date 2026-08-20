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

<h2>Latest Travel Stories</h2>

<?php if (empty($posts)): ?>
    <div class="card">
        <p>No travel stories published yet. Be the first to share one!</p>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="card">
            <h3 style="margin-top: 0; color: #111;"><?= htmlspecialchars($post['title']); ?></h3>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
                Published by <strong><?= htmlspecialchars($post['username']); ?></strong> on <?= date('F j, Y', strtotime($post['created_at'])); ?>
            </p>
            <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 12px;">
                    <a href="editor.php?id=<?= $post['id']; ?>" class="btn" style="background: #555;">Edit</a>
                    <a href="delete.php?id=<?= $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this story?');">Delete</a>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>