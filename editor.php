<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$title = '';
$content = '';
$errors = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM blogPost WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();

    if (!$post || $post['user_id'] != $_SESSION['user_id']) {
        die("Unauthorized or story not found.");
    }

    $title = $post['title'];
    $content = $post['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($content)) { $errors[] = "Story content is required."; }

    if (empty($errors)) {
        if ($id) {
            $updateStmt = $pdo->prepare("UPDATE blogPost SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
            $updateStmt->execute(['title' => $title, 'content' => $content, 'id' => $id, 'user_id' => $_SESSION['user_id']]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (:user_id, :title, :content)");
            $insertStmt->execute(['user_id' => $_SESSION['user_id'], 'title' => $title, 'content' => $content]);
        }

        header("Location: index.php");
        exit;
    }
}
?>

<div class="main-container">
    <div class="card">
        <h2 style="margin-top: 0;"><?= $id ? 'Edit Story' : 'Publish a New Travel Story'; ?></h2>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="editor.php<?= $id ? '?id=' . $id : ''; ?>" method="POST">
            <div class="form-group">
                <label for="title">Story Title</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($title); ?>" placeholder="e.g., Backpacking Across Sri Lanka" required>
            </div>

            <div class="form-group">
                <label for="content">Story Details</label>
                <textarea id="content" name="content" class="form-control" rows="10" placeholder="Describe your travel adventure..." required><?= htmlspecialchars($content); ?></textarea>
            </div>

            <button type="submit" class="btn-primary"><?= $id ? 'Save Changes' : 'Publish Story'; ?></button>
            <a href="index.php" style="margin-left: 15px; color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>