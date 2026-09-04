<?php
require_once '../Includes/db.php';
$pageTitle = 'Spaces | LFT Dumaguete';
$currentPage = 'SPACES';
$spaceUser = currentUser();
$spaces = db()->query('SELECT * FROM spaces WHERE active = 1 ORDER BY id')->fetchAll();
require '../Includes/header.php';
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/common.png');">
        <div class="container">
            <p class="section-label">FIND YOUR FLOW</p>
            <h1>Spaces made for<br><span>your kind of work.</span></h1>
            <p>From focused desks to polished meeting rooms, choose the setting that helps you do your best work.</p>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <div class="page-intro">
                <p class="section-label">AVAILABLE WORKSPACES</p>
                <h2>Choose a space that fits the work.</h2>
            </div>

            <?php if (!$spaces): ?>
                <div class="empty-state">No spaces are currently published. Please contact the LFT team for availability.</div>
            <?php else: ?>
                <div class="listing-grid">
                    <?php foreach ($spaces as $space): ?>
                        <article class="listing-card">
                            <img src="../assets/images/<?= e($space['image']) ?>" alt="<?= e($space['name']) ?>">
                            <div>
                                <p class="section-label"><?= e(strtoupper($space['category'])) ?></p>
                                <h2><?= e($space['name']) ?></h2>
                                <p><?= e($space['description']) ?></p>
                                <span class="space-seats"><i class="fa-solid fa-chair" aria-hidden="true"></i> <?= (int) $space['seats'] ?> <?= (int) $space['seats'] === 1 ? 'seat' : 'seats' ?></span>
                                <strong><?= nl2br(e($space['rates'])) ?></strong>

                                <?php if ($spaceUser && $spaceUser['role'] === 'customer'): ?>
                                    <a class="btn btn-green" href="../Booking/index.php?space=<?= urlencode($space['name']) ?>">BOOK THIS SPACE</a>
                                <?php elseif ($spaceUser && $spaceUser['role'] === 'staff'): ?>
                                    <a class="btn btn-green" href="../Staff/index.php">OPEN STAFF DESK</a>
                                <?php elseif ($spaceUser && $spaceUser['role'] === 'admin'): ?>
                                    <a class="btn btn-green" href="../Admin/spaces/">MANAGE SPACES</a>
                                <?php else: ?>
                                    <a class="btn btn-green" href="../Login/index.php?next=../Booking/index.php?space=<?= urlencode($space['name']) ?>">LOG IN TO BOOK</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
