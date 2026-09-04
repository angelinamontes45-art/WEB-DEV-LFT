<?php
$pageTitle = 'Contact | LFT Dumaguete';
$currentPage = 'CONTACT';
require '../Includes/header.php';
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/main.png');"><div class="container"><p class="section-label">COME BY</p><h1>Let’s find your<br><span>right workspace.</span></h1><p>Tell us what you need, and we’ll help you find the best way to experience LFT Dumaguete.</p></div></section>
    <section class="contact-section" id="tour"><div class="container contact-grid"><div class="contact-copy"><p class="section-label">BOOK A TOUR</p><h2>See the space in person.</h2><p>Complete the form and our team will get back to you with available tour times.</p><div class="contact-detail"><i class="fa-solid fa-location-dot"></i><span>Hibard St. Dumaguete City,<br>Negros Oriental</span></div><div class="contact-detail"><i class="fa-solid fa-envelope"></i><a href="mailto:lftdumaguete@gmail.com">lftdumaguete@gmail.com</a></div><div class="contact-detail"><i class="fa-solid fa-phone"></i><a href="tel:+639912345678">+63 9912345678</a></div></div>
        <div class="tour-form"><p class="section-label">CHOOSE YOUR VISIT</p><h3>Sign in to continue</h3><p>Create an account once, then use the same form for bookings and walk-in check-ins.</p><a class="btn btn-green" href="../Login/index.php?next=../Booking/index.php">LOG IN OR CREATE ACCOUNT</a><a class="text-link" href="../Booking/index.php?type=walk-in">I am already at LFT</a></div>
    </div></section>
</main>
<?php require '../Includes/footer.php'; ?>