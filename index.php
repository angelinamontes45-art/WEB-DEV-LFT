<?php

$pageTitle = "LFT Dumaguete Curated Workspaces";
$currentPage = "HOME";
require_once __DIR__ . "/Includes/db.php";
$homeSpaces = db()->query('SELECT * FROM spaces WHERE active = 1 ORDER BY id')->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= $pageTitle ?>
    </title>

    <!-- CSS -->
    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<?php require __DIR__ . "/Includes/header.php"; ?>


<!-- =========================================
     HERO SECTION
========================================= -->

<section class="hero">

    <div class="hero-overlay"></div>

    <div class="container hero-content">

        <h1>
            Your Space.<br>
            Your Focus.<br>
            <span>Your Community.</span>
        </h1>

        <p class="hero-tagline">
            WORK BETTER. CONNECT MORE. THRIVE TOGETHER.
        </p>

        <p class="hero-description">
            LFT Dumaguete Curated Workspaces is a model
            coworking space designed for productivity,
            creativity and connection.
        </p>

        <div class="hero-buttons">

            <a href="Contact/index.php"
               class="btn btn-outline1">
                BOOK A TOUR
            </a>

            <a href="Spaces/index.php"
               class="btn btn-outline">
                VIEW SPACES
            </a>

        </div>

    </div>

</section>


<!-- =========================================
     FEATURES
========================================= -->

<section class="features">

    <div class="container feature-grid">

        <div class="feature-item">

            <div class="feature-icon"><img src="assets/images/office-chair.svg"></div>

            <h3>CURATED SPACES</h3>

            <p>
                Thoughtfully designed environments
                that help you focus and create.
            </p>

        </div>


        <div class="feature-item">

            <div class="feature-icon"><i class="fa-solid fa-users"></i></div>

            <h3>VIBRANT COMMUNITY</h3>

            <p>
                Connect with inspiring people
                and grow together.
            </p>

        </div>


        <div class="feature-item">

            <div class="feature-icon"><i class="fa-solid fa-mug-hot"></i></i></div>

            <h3>PREMIUM AMENITIES</h3>

            <p>
                Everything you need to work
                comfortably and efficiently.
            </p>

        </div>


        <div class="feature-item">

            <div class="feature-icon"><i class="fa-solid fa-wifi"></i></div>

            <h3>FAST & RELIABLE</h3>

            <p>
                High-speed internet so you can
                stay productive all day.
            </p>

        </div>


        <div class="feature-item">

            <div class="feature-icon"><i class="fa-regular fa-calendar-days"></i></div>

            <h3>FLEXIBLE PLANS</h3>

            <p>
                Flexible options designed
                around your work style.
            </p>

        </div>

    </div>

</section>


<!-- =========================================
     ABOUT
========================================= -->

<section class="about-section">

    <div class="container about-grid">

        <div class="about-content">

            <div class="section-label">
                ABOUT LFT DUMAGUETE
            </div>

            <h2>
                A better way<br>
                to work in Dumaguete.
            </h2>

            <div class="gold-line"></div>

            <p>
                We're more than a workspace.
                We're a community of doers, dreamers,
                and builders—creating a space where
                great work happens and meaningful
                connections are made.
            </p>

            <a href="About/index.php"
               class="btn btn-green">
                LEARN MORE
            </a>

        </div>


        <div class="about-image">

            <img src="assets/images/about.png"
                 alt="LFT Dumaguete Workspace">

        </div>

    </div>

</section>


<!-- =========================================
     SPACES
========================================= -->

<section class="spaces-section">

    <div class="container">

        <div class="spaces-heading">

            <div class="section-label">
                CHOOSE YOUR SPACE
            </div>

            <h2>
                Flexible options for every kind
                of professional.
            </h2>

        </div>


        <div class="space-carousel" data-carousel>

            <button class="carousel-control carousel-prev" type="button" data-carousel-prev aria-label="Previous spaces">
                <i class="fa-solid fa-arrow-left"></i>
            </button>

            <div class="space-grid" data-carousel-track>

            <?php foreach ($homeSpaces as $space): ?>
            <div class="space-card">
                <img src="assets/images/<?= e($space['image']) ?>" alt="<?= e($space['name']) ?>">
                <div class="space-card-content">
                    <h3><?= e(strtoupper($space['name'])) ?></h3>
                    <p><?= e($space['description']) ?></p>
                    <span class="space-seats"><i class="fa-solid fa-chair"></i> <?= (int) $space['seats'] ?> <?= (int) $space['seats'] === 1 ? 'seat' : 'seats' ?></span>
                    <div class="price">FROM <strong><?= nl2br(e($space['rates'])) ?></strong></div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (false): ?>


            <!-- COMMONS -->

            <div class="space-card">

                <img
                    src="assets/images/common.png"
                    alt="LFT Commons">

                <div class="space-card-content">

                    <h3>LFT COMMONS</h3>

                    <p>
                        Perfect for freelancers
                        and digital nomads.
                    </p>

                    <div class="price">
                        FROM <strong>₱199/DAY</strong>
                    </div>

                </div>

            </div>


            <!-- PODCAST -->

            <div class="space-card">

                <img
                    src="assets/images/podcast.png"
                    alt="Podcast Studio">

                <div class="space-card-content">

                    <h3>PODCAST</h3>

                    <p>
                        Good for content creators.
                    </p>

                    <div class="price">
                        FROM <strong>₱650/HOURLY</strong>
                    </div>

                </div>

            </div>


            <!-- CLUB -->

            <div class="space-card">

                <img
                    src="assets/images/the club.png"
                    alt="The Club">

                <div class="space-card-content">

                    <h3>THE CLUB</h3>

                    <p>
                        A premium room with
                        full access.
                    </p>

                    <div class="price">
                        FROM <strong>₱25000/MONTH</strong>
                    </div>

                </div>

            </div>


            <!-- CONFERENCE -->

            <div class="space-card">

                <img
                    src="assets/images/conference.png"
                    alt="Conference Room">

                <div class="space-card-content">

                    <h3>CONFERENCE ROOM</h3>

                    <p>
                        Book by the hour for
                        meetings and sessions.
                    </p>

                    <div class="price">
                        FROM <strong>₱850/HOUR</strong>
                    </div>

                </div>

            </div>


            <!-- LFT RESERVE -->

            <div class="space-card">

                <img
                    src="assets/images/common.png"
                    alt="LFT Reserve room">

                <div class="space-card-content">

                    <h3>LFT RESERVE</h3>

                    <p>
                        A private room reserved
                        by the hour.
                    </p>

                    <div class="price">
                        FROM <strong>₱240/HOUR</strong>
                    </div>

                </div>

            </div>


            <!-- LEARNING STUDIO -->

            <div class="space-card">

                <img src="assets/images/common.png" alt="Learning Studio">

                <div class="space-card-content">
                    <h3>LEARNING STUDIO</h3>
                    <p>Built for classes, study sessions, and workshops.</p>
                    <div class="price">FROM <strong>₱199/HOUR<br>₱548/3 HOURS</strong></div>
                </div>

            </div>


            <!-- FOCUS SUITE -->

            <div class="space-card">

                <img src="assets/images/about.png" alt="Focus Suite">

                <div class="space-card-content">
                    <h3>FOCUS SUITE</h3>
                    <p>A quiet suite for uninterrupted deep work.</p>
                    <div class="price">FROM <strong>₱149/HOUR<br>₱399/3 HOURS</strong></div>
                </div>

            </div>


            <!-- TEAM SUITE -->

            <div class="space-card">

                <img src="assets/images/conference.png" alt="Team Suite">

                <div class="space-card-content">
                    <h3>TEAM SUITE</h3>
                    <p>A collaborative room for growing teams.</p>
                    <div class="price">FROM <strong>₱450/HOUR<br>₱1,600/4 HOURS<br>₱2,800/DAY</strong></div>
                </div>

            </div>


            <!-- PRIVATE OFFICE -->

            <div class="space-card">

                <img src="assets/images/the club.png" alt="Private Office">

                <div class="space-card-content">
                    <h3>PRIVATE OFFICE</h3>
                    <p>A private professional office for focused work.</p>
                    <div class="price">FROM <strong>₱1,250/HOUR<br>₱8,999/DAY</strong></div>
                </div>

            </div>


            <!-- CONTENT STUDIO -->

            <div class="space-card">

                <img src="assets/images/podcast.png" alt="Content Studio">

                <div class="space-card-content">
                    <h3>CONTENT STUDIO</h3>
                    <p>Make polished content, podcasts, and presentations.</p>
                    <div class="price">FROM <strong>₱650/HOUR<br>₱3,999/DAY</strong></div>
                </div>

            </div>

            <?php endif; ?></div>

            <button class="carousel-control carousel-next" type="button" data-carousel-next aria-label="Next spaces">
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </div>


        <div class="center-button">

            <a href="Spaces/index.php"
               class="btn btn-green">
                VIEW ALL SPACES
            </a>

        </div>

    </div>

</section>


<!-- =========================================
     COMMUNITY
========================================= -->

<section class="community-section">

    <div class="container community-grid">

        <div class="community-content">

            <div class="section-label">
                BE PART OF THE COMMUNITY
            </div>

            <h2>
                Work, connect, and grow—
                right here in Dumaguete.
            </h2>

            <p>
                Join a community that inspires
                productivity and supports your journey.
            </p>

            <a href="Membership/index.php"
               class="community-button">
                JOIN OUR COMMUNITY →
            </a>

        </div>


        <div class="stats">

            <div class="stat-item">

                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>

                <strong>100+</strong>

                <span>
                    Active Members
                </span>

            </div>


            <div class="stat-item">

                <div class="stat-icon"><i class="fa-solid fa-mug-hot"></i></div>

                <strong>500+</strong>

                <span>
                    Cups of Coffee<br>
                    (And Counting)
                </span>

            </div>


            <div class="stat-item">

                <div class="stat-icon"><i class="fa-regular fa-calendar-days"></i></div>

                <strong>30+</strong>

                <span>
                    Events & Workshops<br>
                    Each Month
                </span>

            </div>

        </div>

    </div>

</section>


<?php require __DIR__ . "/Includes/footer.php"; ?>
