<?php if (!empty($authLayout)): ?>
</div>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
<?php else: ?>
</main>
<footer class="site-footer">
    <div class="footer-inner">
        <?php
        $footerSiteName = get_setting('site_name', 'Library');
        $footerTagline = get_setting('site_tagline', 'Read online, download, and discover books. View on Web or use our App.');
        ?>
        <p class="footer-brand"><?= e($footerSiteName) ?></p>
        <p class="footer-tagline"><?= e($footerTagline) ?></p>
        <nav class="footer-nav" aria-label="Footer navigation">
            <a href="<?= base_url() ?>">Home</a>
            <a href="<?= base_url('about.php') ?>">About</a>
            <a href="<?= base_url('books/') ?>">Books</a>
            <a href="<?= base_url('themes/') ?>">Categories</a>
            <a href="<?= base_url('auth/login.php') ?>">Login</a>
            <a href="<?= base_url('auth/register.php') ?>">Register</a>
        </nav>
        <p class="footer-copy">&copy; <?= date('Y') ?> <?= e($footerSiteName) ?>. All rights reserved.</p>
    </div>
</footer>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
<?php endif; ?>
