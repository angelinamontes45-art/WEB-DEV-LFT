<?php
$pageTitle = 'Spaces | LFT Dumaguete';
$currentPage = 'SPACES';
require '../Includes/header.php';
$spaces = db()->query('SELECT * FROM spaces WHERE active = 1 ORDER BY id')->fetchAll();
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/common.png');">
        <div class="container"><p class="section-label">FIND YOUR FLOW</p><h1>Spaces made for<br><span>your kind of work.</span></h1><p>From focused desks to polished meeting rooms, choose the setting that helps you do your best work.</p></div>
    </section>
    <section class="listing-section"><div class="container"><div class="listing-grid">
        <?php foreach ($spaces as $space): ?>
        <article class="listing-card"><img src="../assets/images/<?= e($space['image']) ?>" alt="<?= e($space['name']) ?>"><div><p class="section-label"><?= e(strtoupper($space['category'])) ?></p><h2><?= e($space['name']) ?></h2><p><?= e($space['description']) ?></p><span class="space-seats"><i class="fa-solid fa-chair"></i> <?= (int) $space['seats'] ?> <?= (int) $space['seats'] === 1 ? 'seat' : 'seats' ?></span><strong><?= nl2br(e($space['rates'])) ?></strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <?php endforeach; /* Catalog content is maintained from the admin dashboard. */ ?>
        <?php /* Legacy cards are replaced by the database catalog. */ ?>
        <?php if (false): ?>
        <article class="listing-card"><img src="../assets/images/common.png" alt="LFT Commons"><div><p class="section-label">FLEXIBLE DESKS</p><h2>LFT Commons</h2><p>A bright, social workspace for freelancers, students, and remote teams.</p><strong>From ₱199 / day</strong><a class="btn btn-green" href="../Contact/index.php#tour">ASK ABOUT AVAILABILITY</a></div></article>
        <article class="listing-card"><img src="../assets/images/podcast.png" alt="Podcast studio"><div><p class="section-label">CREATOR STUDIO</p><h2>Podcast Studio</h2><p>A ready-to-use recording room for conversations, lessons, and content.</p><strong>From ₱650 / hour</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/the club.png" alt="The Club private workspace"><div><p class="section-label">PRIVATE WORKSPACE</p><h2>The Club</h2><p>A premium room with full access for teams who need room to grow.</p><strong>From ₱25,000 / month</strong><a class="btn btn-green" href="../Contact/index.php#tour">ASK ABOUT AVAILABILITY</a></div></article>
        <article class="listing-card"><img src="../assets/images/conference.png" alt="Conference room"><div><p class="section-label">MEETINGS</p><h2>Conference Room</h2><p>A professional setting for meetings, workshops, and client sessions.</p><strong>From ₱850 / hour</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/common.png" alt="LFT Reserve room"><div><p class="section-label">PRIVATE ROOM</p><h2>LFT Reserve</h2><p>A private room reserved by the hour for focused work and calls.</p><strong>From ₱240 / hour</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/common.png" alt="Learning Studio"><div><p class="section-label">LEARNING & WORKSHOPS</p><h2>Learning Studio</h2><p>A welcoming room for classes, study sessions, and workshops.</p><strong>₱199 / hour<br>₱548 / 3 hours</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/about.png" alt="Focus Suite"><div><p class="section-label">DEEP WORK</p><h2>Focus Suite</h2><p>A quiet suite for uninterrupted focus and productivity.</p><strong>₱149 / hour<br>₱399 / 3 hours</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/conference.png" alt="Team Suite"><div><p class="section-label">TEAM WORKSPACE</p><h2>Team Suite</h2><p>A collaborative room for meetings and growing teams.</p><strong>₱450 / hour<br>₱1,600 / half day (4 hours)<br>₱2,800 / day</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/the club.png" alt="Private Office"><div><p class="section-label">PRIVATE WORKSPACE</p><h2>Private Office</h2><p>A private professional office for focused work and calls.</p><strong>₱1,250 / hour<br>₱8,999 / day</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <article class="listing-card"><img src="../assets/images/podcast.png" alt="Content Studio"><div><p class="section-label">CONTENT CREATION</p><h2>Content Studio</h2><p>A ready-to-use studio for podcasts, presentations, and content.</p><strong>₱650 / hour<br>₱3,999 / day</strong><a class="btn btn-green" href="../Contact/index.php#tour">BOOK A TOUR</a></div></article>
        <?php endif; ?></div></div></section>
</main>
<?php require '../Includes/footer.php'; ?>