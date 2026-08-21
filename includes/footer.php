    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>🧭 Travel Tales</h3>
                    <p>"Stories, journeys, and memories from around the world." Share your personal travel adventures, tips, and photos with a global community of wanderers.</p>
                </div>
                
                <div class="footer-nav">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Explore All Stories</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="index.php?filter=my">My Stories</a></li>
                            <li><a href="editor.php">Create Story</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Log In</a></li>
                            <li><a href="register.php">Create Free Account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-nav">
                    <h4>About Travel Tales</h4>
                    <ul>
                        <li><a href="index.php#about">Our Story</a></li>
                        <li><a href="index.php">Community Guidelines</a></li>
                        <li><a href="index.php">Travel Inspiration</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Travel Tales. All rights reserved.</p>
                <p>Designed with PHP, MySQL &amp; CSS.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Application Script -->
    <script src="js/main.js"></script>
</body>
</html>