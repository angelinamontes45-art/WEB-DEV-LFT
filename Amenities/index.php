<?php
$pageTitle = 'Amenities | LFT Dumaguete';
$currentPage = 'AMENITIES';
require '../Includes/header.php';
$amenities = db()->query('SELECT * FROM amenities WHERE active = 1 ORDER BY id')->fetchAll();
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/about.png');"><div class="container"><p class="section-label">EVERYTHING IN PLACE</p><h1>Designed around<br><span>your best work.</span></h1><p>Thoughtful details keep your day comfortable, connected, and moving forward.</p></div></section>
    <section class="listing-section"><div class="container"><div class="amenity-grid">
        <?php foreach ($amenities as $amenity): ?><article class="amenity-item"><i class="<?= e($amenity['icon']) ?>"></i><h2><?= e($amenity['name']) ?></h2><p><?= e($amenity['description']) ?></p></article><?php endforeach; ?>
    </div><div class="center-button"><a class="btn btn-green" href="../Contact/index.php#tour">COME SEE IT YOURSELF</a></div></div></section>
</main>
<?php require '../Includes/footer.php'; ?>