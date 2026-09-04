<?php
$pageTitle = 'Memberships | LFT Dumaguete';
$currentPage = 'MEMBERSHIPS';
require '../Includes/header.php';
$memberships = db()->query("SELECT * FROM memberships WHERE status = 'Active' ORDER BY id")->fetchAll();
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/the club.png');"><div class="container"><p class="section-label">MAKE ROOM FOR MORE</p><h1>A membership that<br><span>fits your rhythm.</span></h1><p>Choose the access level that supports how you work today, with a community that grows with you.</p></div></section>
    <section class="listing-section"><div class="container"><div class="page-intro"><p class="section-label">MEMBERSHIP OPTIONS</p><h2>Simple access. Serious momentum.</h2></div><div class="plan-grid">
        <?php foreach ($memberships as $membership): ?><article class="plan-card <?= $membership['featured'] ? 'featured-plan' : '' ?>"><p class="section-label"><?= e($membership['label']) ?></p><h3><?= e($membership['name']) ?></h3><strong><?= e($membership['price']) ?> <small>/ <?= e($membership['period']) ?></small></strong><p><?= e($membership['description']) ?></p><?php if (trim($membership['features']) !== ''): ?><ul class="plan-features"><?php foreach (preg_split('/\r?\n/', $membership['features']) as $feature): if (trim($feature) !== ''): ?><li><?= e(trim($feature)) ?></li><?php endif; endforeach; ?></ul><?php endif; ?><a class="btn btn-green" href="../Contact/index.php#tour">GET STARTED</a></article><?php endforeach; ?>
    </div></div></section>
</main>
<?php require '../Includes/footer.php'; ?>