<?php
require '../Includes/db.php';
$user = requireUser();
$errors = [];
$success = false;
$bookingType = ($_GET['type'] ?? 'booking') === 'walk-in' ? 'walk-in' : 'booking';

$spaceStatement = db()->query('SELECT name FROM spaces WHERE active = 1 ORDER BY name');
$availableSpaces = $spaceStatement->fetchAll(PDO::FETCH_COLUMN);

$space = '';
$visitDate = '';
$visitTime = '';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingType = ($_POST['booking_type'] ?? 'booking') === 'walk-in' ? 'walk-in' : 'booking';
    $space = trim($_POST['space'] ?? '');
    $visitDate = trim($_POST['visit_date'] ?? '');
    $visitTime = trim($_POST['visit_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($space === '' || $visitDate === '' || $visitTime === '') {
        $errors[] = 'Please complete the space, date, and time fields.';
    }

    if ($space !== '' && !in_array($space, $availableSpaces, true)) {
        $errors[] = 'Please choose an available space.';
    }

    $dateObject = DateTime::createFromFormat('Y-m-d', $visitDate);
    if ($visitDate !== '' && (!$dateObject || $dateObject->format('Y-m-d') !== $visitDate)) {
        $errors[] = 'Please choose a valid date.';
    } elseif ($visitDate !== '' && $visitDate < date('Y-m-d')) {
        $errors[] = 'Choose today or a future date.';
    }

    $timeObject = DateTime::createFromFormat('H:i', $visitTime);
    if ($visitTime !== '' && (!$timeObject || $timeObject->format('H:i') !== $visitTime)) {
        $errors[] = 'Please choose a valid time.';
    }

    if (!$errors) {
        $statement = db()->prepare('INSERT INTO bookings (user_id, booking_type, space, visit_date, visit_time, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$user['id'], $bookingType, $space, $visitDate, $visitTime, $notes]);
        $success = true;
        $space = $visitDate = $visitTime = $notes = '';
    }
}

$pageTitle = 'Book a Visit | LFT Dumaguete';
$currentPage = 'BOOKING';
require '../Includes/header.php';
?>
<main>
    <section class="auth-section">
        <div class="booking-card">
            <div>
                <p class="section-label"><?= $bookingType === 'walk-in' ? 'WALK-IN CHECK-IN' : 'RESERVE YOUR SPACE' ?></p>
                <h1><?= $bookingType === 'walk-in' ? 'Tell us you are here.' : 'Plan your next workday.' ?></h1>
                <p>Hi <?= e($user['name']) ?>. Fill in the details below and our team will confirm your request.</p>
                <p class="booking-links"><a href="?type=booking">Book a space</a> <span>/</span> <a href="?type=walk-in">Walk-in check-in</a></p>
            </div>

            <?php if ($success): ?>
                <div class="success-message">Your <?= e($bookingType) ?> request was recorded. <a href="../Dashboard/index.php">View your requests</a>.</div>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <div class="form-error"><?= e($error) ?></div>
            <?php endforeach; ?>

            <form class="tour-form" method="post">
                <input type="hidden" name="booking_type" value="<?= e($bookingType) ?>">

                <label for="space">Space</label>
                <select id="space" name="space" required>
                    <option value="">Choose a space</option>
                    <?php foreach ($availableSpaces as $spaceName): ?>
                        <option value="<?= e($spaceName) ?>" <?= $space === $spaceName ? 'selected' : '' ?>><?= e($spaceName) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="visit_date">Date</label>
                <input id="visit_date" name="visit_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($visitDate) ?>" required>

                <label for="visit_time">Time</label>
                <input id="visit_time" name="visit_time" type="time" value="<?= e($visitTime) ?>" required>

                <label for="notes">Notes <span>(optional)</span></label>
                <textarea id="notes" name="notes" rows="4"><?= e($notes) ?></textarea>

                <button class="btn btn-green" type="submit">SUBMIT REQUEST</button>
            </form>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
