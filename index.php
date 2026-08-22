<?php
/**
 * Travel Tales - Main Page & Story View
 *
 * This single file serves both:
 * 1. Single Story View: index.php?id=X
 * 2. All Stories List:  index.php
 */

require_once __DIR__ . '/config/db.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check logged-in user state
$currentUserId = $_SESSION['user_id'] ?? null;
$isLoggedIn = !empty($currentUserId);

$blogId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$filter = $_GET['filter'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');

// ==========================================================
// 1. SINGLE STORY VIEW (index.php?id=X)
// ==========================================================
if (!empty($blogId) && $blogId > 0) {
    try {
        // Query the story and author details using a prepared statement
        $stmt = $pdo->prepare("
            SELECT b.*, u.`username`, u.`email` 
            FROM `blog_posts` b
            JOIN `users` u ON b.`user_id` = u.`id`
            WHERE b.`id` = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $blogId]);
        $post = $stmt->fetch();

        // If story does not exist, show friendly error
        if (!$post) {
            $pageTitle = "Story Not Found";
            require_once __DIR__ . '/includes/header.php';
            ?>
            <div class="container container-narrow" style="padding: 60px 20px;">
                <div class="empty-state">
                    <div class="empty-icon">🏝️</div>
                    <h3>Story Not Found</h3>
                    <p>The travel tale you are looking for may have been removed, deleted, or the link is incorrect.</p>
                    <a href="index.php" class="btn btn-primary">← Back to All Stories</a>
                </div>
            </div>
            <?php
            require_once __DIR__ . '/includes/footer.php';
            exit;
        }

        $pageTitle = $post['title'];
        // Check if the currently logged-in user is the owner of this story
        $isOwner = ($isLoggedIn && (int)$currentUserId === (int)$post['user_id']);
        
        require_once __DIR__ . '/includes/header.php';
        ?>

        <article class="single-blog-article">
            <div class="container container-narrow">
                <!-- Back Navigation -->
                <div class="back-nav">
                    <a href="index.php" class="back-link">← Back to All Stories</a>
                </div>

                <!-- Story Header -->
                <header class="single-blog-header">
                    <h1 class="single-blog-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="single-blog-meta">
                        <div class="author-meta-wrapper">
                            <div class="author-avatar-large">
                                <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                            </div>
                            <div class="author-meta-info">
                                <span class="author-meta-name">By <?php echo htmlspecialchars($post['username']); ?></span>
                                <span class="author-meta-date">
                                    Published on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    <?php if (!empty($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
                                        <small>(Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Owner-only Action Buttons (Edit | Delete) -->
                        <?php if ($isOwner): ?>
                            <div class="single-owner-controls">
                                <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-outline btn-sm">✏️ Edit Story</a>
                                <a href="delete.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger btn-sm js-confirm-delete" 
                                   onclick="return confirm('Are you sure you want to delete this travel story? This action cannot be undone.');">🗑️ Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- Optional Cover Image -->
                <?php if (!empty($post['image'])): ?>
                    <div class="single-featured-media">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                <?php endif; ?>

                <!-- Complete Story Content -->
                <div class="single-blog-content">
                    <?php 
                        // Safely escape content and preserve paragraph breaks
                        echo nl2br(htmlspecialchars($post['content'])); 
                    ?>
                </div>

                <!-- Footer Navigation within Article -->
                <div style="margin-top: 40px; padding: 24px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="author-avatar-large" style="background: var(--accent);">
                            <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <strong style="color: var(--primary); font-size: 1.05rem;">Written by <?php echo htmlspecialchars($post['username']); ?></strong>
                            <div style="color: var(--text-muted); font-size: 0.85rem;">Travel Tales Storyteller</div>
                        </div>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline btn-sm">← Back to Stories</a>
                    </div>
                </div>

            </div>
        </article>

        <?php
        require_once __DIR__ . '/includes/footer.php';
        exit;
    } catch (PDOException $e) {
        error_log("Single Post Fetch Error: " . $e->getMessage());
        die("An error occurred while loading this travel story.");
    }
}

// ==========================================================
// 2. ALL STORIES LIST VIEW (index.php)
// ==========================================================
$pageTitle = "Explore Stories";

// Build SQL query
$sql = "
    SELECT b.*, u.`username` 
    FROM `blog_posts` b
    JOIN `users` u ON b.`user_id` = u.`id`
";
$params = [];
$conditions = [];

// Filter: My Stories
if ($filter === 'my' && $isLoggedIn) {
    $conditions[] = "b.`user_id` = :userId";
    $params[':userId'] = $currentUserId;
}

// Search Filter
if (!empty($searchQuery)) {
    $conditions[] = "(b.`title` LIKE :searchTitle OR b.`content` LIKE :searchContent)";
    $params[':searchTitle'] = '%' . $searchQuery . '%';
    $params[':searchContent'] = '%' . $searchQuery . '%';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY b.`created_at` DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Posts Fetch Error: " . $e->getMessage());
    $posts = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner (Shown on default home page) -->
<?php if (empty($filter) && empty($searchQuery)): ?>
    <section class="hero-section">
        <div class="container">
            <span class="hero-tagline">✨ Footprints &amp; Memories</span>
            <h1 class="hero-title">Go beyond the destination, <br> Discover the journey.</h1>
            <p class="hero-subtitle">Explore inspiring travel stories, unforgettable experiences, hidden places, and practical guides from travelers around the globe.</p>
            <div class="hero-actions">
                <a href="#stories" class="btn btn-primary btn-lg">Explore Stories 🧭</a>
                <?php if ($isLoggedIn): ?>
                    <a href="editor.php" class="btn btn-outline btn-lg" style="color: #fff; border-color: rgba(255,255,255,0.4);">✍️ Create Story</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-outline btn-lg" style="color: #fff; border-color: rgba(255,255,255,0.4);">Join Travel Tales</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Main Stories Container -->
<section id="stories" class="container" style="padding-top: 20px;">
    
    <!-- Section Toolbar: Title, Tabs & Search -->
    <div class="section-toolbar">
        <div>
            <h2 class="section-title">
                <?php 
                    if ($filter === 'my') {
                        echo "My Stories";
                    } elseif (!empty($searchQuery)) {
                        echo "Search Results for \"" . htmlspecialchars($searchQuery) . "\"";
                    } else {
                        echo "Latest Travel Stories";
                    }
                ?>
            </h2>
        </div>

        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <!-- Filter Tabs for Logged-In Users -->
            <?php if ($isLoggedIn): ?>
                <div class="filter-tabs">
                    <a href="index.php" class="filter-tab <?php echo ($filter !== 'my') ? 'active' : ''; ?>">All Stories</a>
                    <a href="index.php?filter=my" class="filter-tab <?php echo ($filter === 'my') ? 'active' : ''; ?>">My Stories (<?php 
                        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `blog_posts` WHERE `user_id` = :uid");
                        $countStmt->execute([':uid' => $currentUserId]);
                        echo (int)$countStmt->fetchColumn();
                    ?>)</a>
                </div>
            <?php endif; ?>

            <!-- Search Form -->
            <form action="index.php" method="GET" class="search-box">
                <?php if ($filter === 'my'): ?>
                    <input type="hidden" name="filter" value="my">
                <?php endif; ?>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search destinations..." aria-label="Search stories">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Blog Posts Grid (Cards) -->
    <?php if (!empty($posts)): ?>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <?php 
                    // Ownership check for displaying Edit/Delete buttons
                    $isPostOwner = ($isLoggedIn && (int)$currentUserId === (int)$post['user_id']); 
                    
                    // Short content excerpt
                    $excerpt = mb_substr(strip_tags($post['content']), 0, 135);
                    if (mb_strlen(strip_tags($post['content'])) > 135) {
                        $excerpt .= '...';
                    }
                ?>
                <article class="blog-card">
                    <div class="blog-card-media">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 loading="lazy">
                        <?php else: ?>
                            <div class="blog-card-placeholder">
                                ✈️
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <span class="blog-author">By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span>•</span>
                            <span class="blog-date">📅 <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>

                        <!-- Story Title -->
                        <h3 class="blog-title">
                            <a href="index.php?id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>

                        <!-- Short Excerpt -->
                        <p class="blog-excerpt">
                            <?php echo htmlspecialchars($excerpt); ?>
                        </p>

                        <!-- Card Footer with Read More & Owner Actions -->
                        <div class="blog-card-footer">
                            <a href="index.php?id=<?php echo $post['id']; ?>" class="read-more-link">
                                Read More →
                            </a>

                            <!-- Edit / Delete Buttons ONLY for the author who owns this story -->
                            <?php if ($isPostOwner): ?>
                                <div class="card-owner-actions">
                                    <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-outline btn-sm" title="Edit story">Edit</a>
                                    <a href="delete.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-danger btn-sm js-confirm-delete" 
                                       onclick="return confirm('Are you sure you want to delete this travel story? This action cannot be undone.');" 
                                       title="Delete story">Delete</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">🗺️</div>
            <?php if (!empty($searchQuery)): ?>
                <h3>No stories found matching "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                <p>Try searching for different keywords or explore all travel tales.</p>
                <a href="index.php<?php echo ($filter === 'my') ? '?filter=my' : ''; ?>" class="btn btn-outline">Clear Search</a>
            <?php elseif ($filter === 'my'): ?>
                <h3>You haven't written any travel stories yet</h3>
                <p>Share your favorite destinations, hidden gems, and travel tips with the world.</p>
                <a href="editor.php" class="btn btn-primary">✍️ Create Your First Story</a>
            <?php else: ?>
                <h3>No travel stories published yet</h3>
                <p>Be the very first traveler to publish an inspiring adventure on Travel Tales!</p>
                <?php if ($isLoggedIn): ?>
                    <a href="editor.php" class="btn btn-primary">✍️ Create Story</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Join &amp; Publish</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
