<?php
require_once '../Includes/db.php';
$contactUser = currentUser();
$connection = db();
$settings = $connection->query('SELECT * FROM site_settings WHERE id = 1')->fetch() ?: [];
$contactSuccess = '';
$contactErrors = [];
$sender = $contactUser['name'] ?? '';
$senderEmail = $contactUser['email'] ?? '';
$subject = '';
$body = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'contact-message') {
    $sender = trim($_POST['sender'] ?? '');
    $senderEmail = strtolower(trim($_POST['email'] ?? ''));
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');

    if ($sender === '') $contactErrors[] = 'Please enter your name.';
    if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) $contactErrors[] = 'Please enter a valid email address.';
    if ($subject === '') $contactErrors[] = 'Please enter a subject.';
    if (strlen($body) < 10) $contactErrors[] = 'Please tell us a little more about your inquiry.';

    if (!$contactErrors) {
        $statement = $connection->prepare('INSERT INTO messages (sender, email, subject, message) VALUES (?, ?, ?, ?)');
        $statement->execute([$sender, $senderEmail, $subject, $body]);
        $contactSuccess = 'Thanks! Your message has been sent to the LFT team.';
        $subject = '';
        $body = '';
    }
}

$siteEmail = $settings['email'] ?? 'lftdumaguete@gmail.com';
$sitePhone = $settings['phone'] ?? '+63 9912345678';
$siteAddress = $settings['address'] ?? 'Hibard St. Dumaguete City, Negros Oriental';
$phoneHref = preg_replace('/[^0-9+]/', '', $sitePhone);

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
                <div class="contact-detail"><i class="fa-solid fa-location-dot"></i><span><?= nl2br(e($siteAddress)) ?></span></div>
                <div class="contact-detail"><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e($siteEmail) ?>"><?= e($siteEmail) ?></a></div>
                <div class="contact-detail"><i class="fa-solid fa-phone"></i><a href="tel:<?= e($phoneHref) ?>"><?= e($sitePhone) ?></a></div>
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
                    <h3>Sign in to reserve a space</h3>
                    <p>Create an account once, then use it to reserve spaces and manage your visits.</p>
                    <a class="btn btn-green" href="../Login/index.php?next=../Booking/index.php">LOG IN OR CREATE ACCOUNT</a>
                    <a class="text-link" href="../Login/index.php?next=../Booking/index.php?type=walk-in">I am already at LFT</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="listing-section">
        <div class="container contact-grid">
            <div class="contact-copy">
                <p class="section-label">SEND A MESSAGE</p>
                <h2>Need help before booking?</h2>
                <p>Send your question directly to the LFT team. It will appear in the admin inbox for follow-up.</p>
            </div>

            <form class="tour-form" method="post" action="#message-us">
                <span id="message-us"></span>
                <input type="hidden" name="action" value="contact-message">
                <?php if ($contactSuccess): ?><div class="success-message"><?= e($contactSuccess) ?></div><?php endif; ?>
                <?php foreach ($contactErrors as $contactError): ?><div class="form-error"><?= e($contactError) ?></div><?php endforeach; ?>

                <label for="sender">Name</label>
                <input id="sender" name="sender" value="<?= e($sender) ?>" required>

                <label for="contact-email">Email</label>
                <input id="contact-email" name="email" type="email" value="<?= e($senderEmail) ?>" required>

                <label for="subject">Subject</label>
                <input id="subject" name="subject" value="<?= e($subject) ?>" required>

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" minlength="10" required><?= e($body) ?></textarea>

                <button class="btn btn-green" type="submit">SEND MESSAGE</button>
            </form>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
