<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Fetch stories joined with author username
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
    <p>No travel stories published yet. Be the first to share one!</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article style="border-bottom: 1px solid #ccc; padding-bottom: 15px; margin-bottom: 20px;">
            <h3><?= htmlspecialchars($post['title']); ?></h3>
            <p><small>By <strong><?= htmlspecialchars($post['username']); ?></strong> on <?= date('F j, Y', strtotime($post['created_at'])); ?></small></p>
            <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                <p>
                    <a href="editor.php?id=<?= $post['id']; ?>">Edit</a> | 
                    <a href="delete.php?id=<?= $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this story?');" style="color: red;">Delete</a>
                </p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>