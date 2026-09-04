<?php
require '../Includes/db.php';
$user = requireUser();
$errors = [];
$success = false;
$bookingType = ($_GET['type'] ?? 'booking') === 'walk-in' ? 'walk-in' : 'booking';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingType = $_POST['booking_type'] === 'walk-in' ? 'walk-in' : 'booking';
    $space = trim($_POST['space'] ?? '');
    $visitDate = $_POST['visit_date'] ?? '';
    $visitTime = $_POST['visit_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    if ($space === '' || $visitDate === '' || $visitTime === '') $errors[] = 'Please complete the space, date, and time fields.';
    if ($visitDate < date('Y-m-d')) $errors[] = 'Choose today or a future date.';
    if (!$errors) {
        $statement = db()->prepare('INSERT INTO bookings (user_id, booking_type, space, visit_date, visit_time, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$user['id'], $bookingType, $space, $visitDate, $visitTime, $notes]);
        $success = true;
    }
}
$pageTitle = 'Book a Visit | LFT Dumaguete';
$currentPage = 'CONTACT';
require '../Includes/header.php';
?>
<main><section class="auth-section"><div class="booking-card"><div><p class="section-label"><?= $bookingType === 'walk-in' ? 'WALK-IN CHECK-IN' : 'RESERVE YOUR SPACE' ?></p><h1><?= $bookingType === 'walk-in' ? 'Tell us you are here.' : 'Plan your next workday.' ?></h1><p>Hi <?= e($user['name']) ?>. Fill in the details below and our team will confirm your request.</p><p class="booking-links"><a href="?type=booking">Book a space</a> <span>/</span> <a href="?type=walk-in">Walk-in check-in</a></p></div><?php if ($success): ?><div class="success-message">Your <?= e($bookingType) ?> request was recorded. <a href="../Dashboard/index.php">View your requests</a>.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?><form class="tour-form" method="post"><input type="hidden" name="booking_type" value="<?= e($bookingType) ?>"><label for="space">Space</label><select id="space" name="space" required><option value="">Choose a space</option><option>LFT Commons</option><option>Podcast Studio</option><option>The Club</option><option>Conference Room</option></select><label for="visit_date">Date</label><input id="visit_date" name="visit_date" type="date" min="<?= date('Y-m-d') ?>" required><label for="visit_time">Time</label><input id="visit_time" name="visit_time" type="time" required><label for="notes">Notes <span>(optional)</span></label><textarea id="notes" name="notes" rows="4"></textarea><button class="btn btn-green" type="submit">SUBMIT REQUEST</button></form></div></section></main>
<?php require '../Includes/footer.php'; ?>