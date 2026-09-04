<?php
$currentPage = 'ABOUT';
include '../Includes/header.php';
?>
<link rel="stylesheet"
          href="../assets/css/about.css">

<main>

    <!-- ================= HERO ================= -->
    <section class="about-hero">

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1>About Us</h1>
            <p>A better workspace. A stronger community.</p>
            <span class="hero-line"></span>
        </div>

    </section>


    <!-- ================= WHO WE ARE ================= -->
    <section class="who-section">

        <div class="who-container">

            <div class="who-text">

                <span class="section-label">WHO WE ARE</span>

                <h2>LFT Dumaguete</h2>

                <p>
                    LFT Dumaguete Curated Workspaces is more than just a place to work –
                    it’s a space designed to inspire productivity, creativity, and connection.
                </p>

                <p>
                    We created LFT to provide a modern and comfortable environment where
                    freelancers, students, entrepreneurs, and professionals can focus,
                    learn, and grow together.
                </p>

                <a href="#" class="story-btn">
                    OUR STORY
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>


            <div class="who-image">
                <img
                    src="../assets/images/about.png"
                    alt="LFT Dumaguete Workspace"
                >
            </div>

        </div>

    </section>


    <!-- ================= MISSION & VISION ================= -->
    <section class="mission-section">

        <div class="mission-container">

            <div class="mission-box">

                <div class="big-icon">
                    <i class="fa-solid fa-bullseye"></i>
                </div>

                <div>
                    <h2>Our Mission</h2>

                    <p>
                        To create a productive and welcoming workspace where
                        people can work smarter, connect better, and achieve more.
                    </p>
                </div>

            </div>


            <div class="divider"></div>


            <div class="mission-box">

                <div class="big-icon eye-icon">
                    <i class="fa-solid fa-eye"></i>
                </div>

                <div>
                    <h2>Our Vision</h2>

                    <p>
                        To become Dumaguete’s leading curated workspace,
                        fostering innovation, collaboration, and lifelong growth.
                    </p>
                </div>

            </div>

        </div>

    </section>


    <!-- ================= VALUES ================= -->
    <section class="values-section">

        <div class="values-heading">

            <div class="heading-line"></div>

            <span>OUR VALUES</span>

            <div class="heading-line"></div>

            <p>The principles that guide everything we do.</p>

        </div>


        <div class="values-container">

            <!-- QUALITY -->
            <div class="value-item">

                <div class="value-icon">
                    <i class="fa-regular fa-gem"></i>
                </div>

                <div class="value-text">
                    <h3>Quality</h3>

                    <p>
                        A well-designed space with the best tools and comforts.
                    </p>
                </div>

            </div>


            <!-- COMMUNITY -->
            <div class="value-item">

                <div class="value-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="value-text">
                    <h3>Community</h3>

                    <p>
                        A space where ideas, skills, and opportunities grow.
                    </p>
                </div>

            </div>


            <!-- INNOVATION -->
            <div class="value-item">

                <div class="value-icon">
                    <i class="fa-regular fa-lightbulb"></i>
                </div>

                <div class="value-text">
                    <h3>Innovation</h3>

                    <p>
                        Encouraging creativity and new ways of thinking.
                    </p>
                </div>

            </div>


            <!-- COLLABORATION -->
            <div class="value-item">

                <div class="value-icon">
                    <i class="fa-regular fa-handshake"></i>
                </div>

                <div class="value-text">
                    <h3>Collaboration</h3>

                    <p>
                        Building connections that create a stronger tomorrow.
                    </p>
                </div>

            </div>

        </div>

    </section>


    <!-- ================= CTA ================= -->
    <section class="cta-section">

        <div class="cta-overlay"></div>

        <div class="cta-content">

            <div>
                <h2>Ready to be part of our community?</h2>

                <p>
                    Book a tour today and experience LFT Dumaguete for yourself.
                </p>
            </div>

            <a href="../Contact/index.php#tour" class="cta-btn">
                BOOK A TOUR
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>

</main>

<?php include '../Includes/footer.php'; ?>