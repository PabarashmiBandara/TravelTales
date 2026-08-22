<?php
/**
 * Travel Tales - Delete Story
 *
 * Deletes a story after verifying user login and ownership.
 * Prevents users from deleting stories owned by other authors.
 */

require_once __DIR__ . '/config/db.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check: Must be logged in
if (empty($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Please log in to delete a story.";
    header("Location: login.php");
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];
$blogId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Validate Story ID
if ($blogId <= 0) {
    $_SESSION['flash_error'] = "Invalid story ID.";
    header("Location: index.php");
    exit;
}

try {
    // 3. Find the story in the database
    $stmt = $pdo->prepare("SELECT `id`, `user_id`, `title` FROM `blog_posts` WHERE `id` = :id LIMIT 1");
    $stmt->execute([':id' => $blogId]);
    $story = $stmt->fetch();

    if (!$story) {
        $_SESSION['flash_error'] = "Story not found.";
        header("Location: index.php");
        exit;
    }

    // 4. CRITICAL OWNERSHIP CHECK: Ensure logged-in user owns the story
    if ((int)$story['user_id'] !== $currentUserId) {
        $_SESSION['flash_error'] = "Authorization Denied: You can only delete your own stories.";
        header("Location: index.php");
        exit;
    }

    // 5. Delete the story from the database (safeguarded by user_id)
    $deleteStmt = $pdo->prepare("DELETE FROM `blog_posts` WHERE `id` = :id AND `user_id` = :user_id");
    $deleteStmt->execute([
        ':id'      => $blogId,
        ':user_id' => $currentUserId
    ]);

    // 6. Set success message and redirect to home
    $_SESSION['flash_success'] = "Story deleted successfully!";
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    error_log("Delete Story Error: " . $e->getMessage());
    $_SESSION['flash_error'] = "A database error occurred while trying to delete the story.";
    header("Location: index.php");
    exit;
}