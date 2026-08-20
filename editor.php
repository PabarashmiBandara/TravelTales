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

    if (!$post) {
        die("Story not found.");
    }

    if ($post['user_id'] != $_SESSION['user_id']) {
        die("Unauthorized access! You can only edit your own stories.");
    }

    $title = $post['title'];
    $content = $post['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($content)) { $errors[] = "Content is required."; }

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

<div class="card">
    <h2><?= $id ? 'Edit Travel Story' : 'Publish a New Travel Story'; ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="editor.php<?= $id ? '?id=' . $id : ''; ?>" method="POST">
        <label for="title">Story Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required placeholder="e.g., Backpacking Across Sri Lanka">

        <label for="content">Story Content</label>
        <textarea id="content" name="content" rows="10" required placeholder="Describe your travel adventure..."><?= htmlspecialchars($content); ?></textarea>

        <button type="submit" class="btn"><?= $id ? 'Update Story' : 'Publish Story'; ?></button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>