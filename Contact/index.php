<?php
require_once '../Includes/db.php';
$contactUser = currentUser();
$pageTitle = 'Contact | LFT Dumaguete';
$currentPage = 'CONTACT';
require '../Includes/header.php';
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/main.png');">
        <div class="container">
            <p class="section-label">COME BY</p>
            <h1>Let’s find your<br><span>right workspace.</span></h1>
            <p>Tell us what you need, and we’ll help you find the best way to experience LFT Dumaguete.</p>
        </div>
    </section>

    <section class="contact-section" id="tour">
        <div class="container contact-grid">
            <div class="contact-copy">
                <p class="section-label">VISIT LFT</p>
                <h2>See the space in person.</h2>
                <p>Reserve a workspace, check in when you arrive, or contact our team if you need help choosing the right setup.</p>
                <div class="contact-detail"><i class="fa-solid fa-location-dot"></i><span>Hibard St. Dumaguete City,<br>Negros Oriental</span></div>
                <div class="contact-detail"><i class="fa-solid fa-envelope"></i><a href="mailto:lftdumaguete@gmail.com">lftdumaguete@gmail.com</a></div>
                <div class="contact-detail"><i class="fa-solid fa-phone"></i><a href="tel:+639912345678">+63 9912345678</a></div>
            </div>

            <div class="tour-form">
                <?php if ($contactUser && $contactUser['role'] === 'customer'): ?>
                    <p class="section-label">WELCOME BACK</p>
                    <h3>Hi <?= e($contactUser['name']) ?>, what would you like to do?</h3>
                    <p>Your account is ready. Choose a visit option below or open your dashboard to manage existing requests.</p>
                    <a class="btn btn-green" href="../Booking/index.php">BOOK A SPACE</a>
                    <a class="btn btn-outline1" href="../Booking/index.php?type=walk-in">WALK-IN CHECK-IN</a>
                    <a class="text-link" href="../Dashboard/index.php">View my dashboard</a>
                <?php elseif ($contactUser && in_array($contactUser['role'], ['staff', 'admin'], true)): ?>
                    <p class="section-label">TEAM ACCESS</p>
                    <h3>You’re signed in as <?= e(ucfirst($contactUser['role'])) ?>.</h3>
                    <p>Use your operations workspace to manage bookings and guest activity.</p>
                    <a class="btn btn-green" href="<?= $contactUser['role'] === 'admin' ? '../Admin/index.php' : '../Staff/index.php' ?>">OPEN <?= strtoupper(e($contactUser['role'])) ?> WORKSPACE</a>
                <?php else: ?>
                    <p class="section-label">CHOOSE YOUR VISIT</p>
                    <h3>Sign in to continue</h3>
                    <p>Create an account once, then use it to reserve spaces and manage your visits.</p>
                    <a class="btn btn-green" href="../Login/index.php?next=../Booking/index.php">LOG IN OR CREATE ACCOUNT</a>
                    <a class="text-link" href="../Login/index.php?next=../Booking/index.php?type=walk-in">I am already at LFT</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
