<?php
$pageTitle = 'LFT Dumaguete Curated Workspaces';
$currentPage = 'HOME';
require_once __DIR__ . '/Includes/db.php';

$connection = db();
$homeSpaces = $connection->query('SELECT * FROM spaces WHERE active = 1 ORDER BY id')->fetchAll();
$homeUser = currentUser();

require __DIR__ . '/Includes/header.php';
?>

<main>
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1>Your Space.<br>Your Focus.<br><span>Your Community.</span></h1>
            <p class="hero-tagline">WORK BETTER. CONNECT MORE. THRIVE TOGETHER.</p>
            <p class="hero-description">LFT Dumaguete Curated Workspaces is a coworking space designed for productivity, creativity, and connection.</p>

            <div class="hero-buttons">
                <?php if ($homeUser && $homeUser['role'] === 'customer'): ?>
                    <a href="Booking/index.php" class="btn btn-outline1">BOOK A SPACE</a>
                    <a href="Dashboard/index.php" class="btn btn-outline">MY DASHBOARD</a>
                <?php elseif ($homeUser && $homeUser['role'] === 'staff'): ?>
                    <a href="Staff/index.php" class="btn btn-outline1">OPEN STAFF DESK</a>
                    <a href="Spaces/index.php" class="btn btn-outline">VIEW SPACES</a>
                <?php elseif ($homeUser && $homeUser['role'] === 'admin'): ?>
                    <a href="Admin/index.php" class="btn btn-outline1">OPEN ADMIN</a>
                    <a href="Spaces/index.php" class="btn btn-outline">VIEW SPACES</a>
                <?php else: ?>
                    <a href="Contact/index.php#tour" class="btn btn-outline1">BOOK A TOUR</a>
                    <a href="Spaces/index.php" class="btn btn-outline">VIEW SPACES</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container feature-grid">
            <div class="feature-item">
                <div class="feature-icon"><img src="assets/images/office-chair.svg" alt="Office chair icon"></div>
                <h3>CURATED SPACES</h3>
                <p>Thoughtfully designed environments that help you focus and create.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                <h3>VIBRANT COMMUNITY</h3>
                <p>Connect with inspiring people and grow together.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-mug-hot" aria-hidden="true"></i></div>
                <h3>PREMIUM AMENITIES</h3>
                <p>Everything you need to work comfortably and efficiently.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-wifi" aria-hidden="true"></i></div>
                <h3>FAST &amp; RELIABLE</h3>
                <p>High-speed internet so you can stay productive all day.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-regular fa-calendar-days" aria-hidden="true"></i></div>
                <h3>FLEXIBLE PLANS</h3>
                <p>Flexible options designed around your work style.</p>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="container about-grid">
            <div class="about-content">
                <div class="section-label">ABOUT LFT DUMAGUETE</div>
                <h2>A better way<br>to work in Dumaguete.</h2>
                <div class="gold-line"></div>
                <p>We’re more than a workspace. We’re a community of doers, dreamers, and builders—creating a place where great work happens and meaningful connections are made.</p>
                <a href="About/index.php" class="btn btn-green">LEARN MORE</a>
            </div>
            <div class="about-image">
                <img src="assets/images/about.png" alt="LFT Dumaguete workspace">
            </div>
        </div>
    </section>

    <section class="spaces-section">
        <div class="container">
            <div class="spaces-heading">
                <div class="section-label">CHOOSE YOUR SPACE</div>
                <h2>Flexible options for every kind of professional.</h2>
            </div>

            <?php if ($homeSpaces): ?>
                <div class="space-carousel" data-carousel>
                    <button class="carousel-control carousel-prev" type="button" data-carousel-prev aria-label="Previous spaces"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
                    <div class="space-grid" data-carousel-track>
                        <?php foreach ($homeSpaces as $space): ?>
                            <article class="space-card">
                                <img src="assets/images/<?= e($space['image']) ?>" alt="<?= e($space['name']) ?>">
                                <div class="space-card-content">
                                    <h3><?= e(strtoupper($space['name'])) ?></h3>
                                    <p><?= e($space['description']) ?></p>
                                    <span class="space-seats"><i class="fa-solid fa-chair" aria-hidden="true"></i> <?= (int) $space['seats'] ?> <?= (int) $space['seats'] === 1 ? 'seat' : 'seats' ?></span>
                                    <div class="price">FROM <strong><?= nl2br(e($space['rates'])) ?></strong></div>
                                    <?php if ($homeUser && $homeUser['role'] === 'customer'): ?>
                                        <a class="text-link" href="Booking/index.php?space=<?= urlencode($space['name']) ?>">Book this space</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control carousel-next" type="button" data-carousel-next aria-label="Next spaces"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                </div>
            <?php else: ?>
                <div class="empty-state">No spaces are currently published. Please check again soon.</div>
            <?php endif; ?>

            <div class="center-button"><a href="Spaces/index.php" class="btn btn-green">VIEW ALL SPACES</a></div>
        </div>
    </section>

    <section class="community-section">
        <div class="container community-grid">
            <div class="community-content">
                <div class="section-label">BE PART OF THE COMMUNITY</div>
                <h2>Work, connect, and grow—right here in Dumaguete.</h2>
                <p>Join a community that inspires productivity and supports your journey.</p>
                <a href="Membership/index.php" class="community-button">EXPLORE MEMBERSHIPS →</a>
            </div>
            <div class="stats">
                <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div><strong>100+</strong><span>Active Members</span></div>
                <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-mug-hot" aria-hidden="true"></i></div><strong>500+</strong><span>Cups of Coffee<br>(And Counting)</span></div>
                <div class="stat-item"><div class="stat-icon"><i class="fa-regular fa-calendar-days" aria-hidden="true"></i></div><strong>30+</strong><span>Events &amp; Workshops</span></div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/Includes/footer.php'; ?>
