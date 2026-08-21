<?php
//Create & Edit Stories

require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check: Must be logged in to create or edit stories
if (empty($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Please log in to write or edit a travel story.";
    header("Location: login.php");
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];
$blogId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEditMode = (!empty($blogId) && $blogId > 0);

$title = '';
$content = '';
$image = '';
$error = '';

if ($isEditMode) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $blogId]);
        $existingStory = $stmt->fetch();

        if (!$existingStory) {
            $_SESSION['flash_error'] = "Story not found.";
            header("Location: index.php");
            exit;
        }

        // Prevent users from editing another user's story
        if ((int)$existingStory['user_id'] !== $currentUserId) {
            $_SESSION['flash_error'] = "Authorization Denied: You can only edit your own stories.";
            header("Location: index.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $title   = $existingStory['title'];
            $content = $existingStory['content'];
            $image   = $existingStory['image'] ?? '';
        }
    } catch (PDOException $e) {
        error_log("Editor Fetch Error: " . $e->getMessage());
        $_SESSION['flash_error'] = "Unable to load story for editing.";
        header("Location: index.php");
        exit;
    }
}

// 3. Process Form Submission (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image   = trim($_POST['image'] ?? '');

    // Form Validations
    if (empty($title)) {
        $error = "Please enter a story title.";
    } elseif (mb_strlen($title) > 255) {
        $error = "Title cannot exceed 255 characters.";
    } elseif (empty($content)) {
        $error = "Story content cannot be empty.";
    } elseif (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
        $error = "Please enter a valid Image URL or leave it blank.";
    } else {
        try {
            if ($isEditMode) {
                // EDIT MODE: Update existing story
                $updateStmt = $pdo->prepare("
                    UPDATE blog_posts 
                    SET title = :title, content = :content, image = :image, updated_at = NOW()
                    WHERE id = :id AND user_id = :user_id
                ");
                $updateStmt->execute([
                    ':title'   => $title,
                    ':content' => $content,
                    ':image'   => !empty($image) ? $image : null,
                    ':id'      => $blogId,
                    ':user_id' => $currentUserId
                ]);

                $_SESSION['flash_success'] = "Story updated successfully!";
                header("Location: index.php");
                exit;
            } else {
                //Insert a new story
                $insertStmt = $pdo->prepare("
                    INSERT INTO blog_posts (user_id, title, content, image, created_at)
                    VALUES (:user_id, :title, :content, :image, NOW())
                ");
                $insertStmt->execute([
                    ':user_id' => $currentUserId,
                    ':title'   => $title,
                    ':content' => $content,
                    ':image'   => !empty($image) ? $image : null
                ]);

                $_SESSION['flash_success'] = "Story published successfully!";
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Editor Save Error: " . $e->getMessage());
            $error = "An error occurred while saving your story. Please try again.";
        }
    }
}

$pageTitle = $isEditMode ? "Edit Story" : "Create Story";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container container-narrow" style="padding: 40px 20px 80px;">
    
    <div class="back-nav">
        <a href="index.php" class="back-link">
            ← Cancel and Return Home
        </a>
    </div>

    <div class="form-card">
        <div class="form-header" style="text-align: left; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
            <h2><?php echo $isEditMode ? '✏️ Edit Story' : '✍️ Create Story'; ?></h2>
            <p><?php echo $isEditMode ? 'Update your story details, narrative, or cover image below.' : 'Share your journey, tips, and experiences with the Travel Tales community.'; ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️ <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="editor.php<?php echo $isEditMode ? '?id=' . $blogId : ''; ?>" method="POST" id="storyEditorForm">
            
            <!-- Story Title -->
            <div class="form-group">
                <label for="title">Story Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?php echo htmlspecialchars($title); ?>" 
                       placeholder="e.g. Sunset Walks along the Historic Ramparts of Galle Fort" 
                       required autofocus>
                <div class="form-help">Enter a clear and engaging headline for your story.</div>
            </div>

            <!-- Image URL -->
            <div class="form-group">
                <label for="imageUrlInput">Image URL <small style="color: var(--text-muted); font-weight: normal;">(Optional)</small></label>
                <input type="url" id="imageUrlInput" name="image" class="form-control" 
                       value="<?php echo htmlspecialchars($image); ?>" 
                       placeholder="https://images.unsplash.com/... or any valid image link">
                <div class="form-help">Paste a web link to a photograph or leave blank.</div>
                
                <!-- Live Image Preview Box -->
                <div class="image-preview-wrapper" id="imagePreviewWrapper">
                    <img id="imagePreview" src="" alt="Story Cover Preview">
                </div>
            </div>

            <!-- Story Content -->
            <div class="form-group">
                <label for="content">Story Content <span class="required">*</span></label>
                <textarea id="content" name="content" class="form-control" rows="12" 
                          placeholder="Tell your travel story here... Describe the sights, sounds, food, and memorable moments." required><?php echo htmlspecialchars($content); ?></textarea>
                <div class="form-help">Separate paragraphs with blank lines.</div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="index.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <?php echo $isEditMode ? 'Update Story' : 'Publish Story'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>