<?php
require_once __DIR__ . '/db.php';
$footerSettings = db()->query('SELECT * FROM site_settings WHERE id = 1')->fetch() ?: [];
$footerName = $footerSettings['website_name'] ?? 'LFT Dumaguete';
$footerEmail = $footerSettings['email'] ?? 'lftdumaguete@gmail.com';
$footerPhone = $footerSettings['phone'] ?? '+63 9912345678';
$footerAddress = $footerSettings['address'] ?? 'Hibard St. Dumaguete City, Negros Oriental';
$facebook = trim($footerSettings['facebook'] ?? '');
$instagram = trim($footerSettings['instagram'] ?? '');
$phoneHref = preg_replace('/[^0-9+]/', '', $footerPhone);
?>
<footer class="site-footer">
    <div class="container footer-container">
        <div class="footer-brand">
            <p class="footer-eyebrow">LFT DUMAGUETE</p>
            <p class="footer-heading"><?= e($footerName) ?><br>Curated Workspaces</p>
        </div>

        <div class="footer-contact">
            <p class="footer-label">Visit us</p>
            <p class="footer-text"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= nl2br(e($footerAddress)) ?></p>
        </div>

        <div class="footer-contact">
            <p class="footer-label">Get in touch</p>
            <a class="footer-text" href="mailto:<?= e($footerEmail) ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <?= e($footerEmail) ?></a>
            <a class="footer-text" href="tel:<?= e($phoneHref) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e($footerPhone) ?></a>
        </div>

        <div class="social-links">
            <p class="footer-label">Follow along</p>
            <div class="social-icons">
                <?php if ($facebook !== ''): ?>
                    <a href="<?= e($facebook) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <?php endif; ?>
                <?php if ($instagram !== ''): ?>
                    <a href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-copyright">
            © <?= date('Y') ?> <?= e($footerName) ?> Curated Workspaces. All Rights Reserved.
            <a class="programmer-link" href="https://www.facebook.com/angelina.montes.378" target="_blank" rel="noopener noreferrer">
                <i class="fa-brands fa-facebook"></i> Programmed by Angelina Montes
            </a>
        </div>
    </div>
</footer>

<script src="<?= $basePath ?? '' ?>assets/js/main.js"></script>
</body>
</html>
