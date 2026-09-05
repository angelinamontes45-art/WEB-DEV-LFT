<?php
require '../Includes/db.php';
$user = requireUser();
$connection = db();
$errors = [];

$bookingType = ($_GET['type'] ?? 'booking') === 'walk-in' ? 'walk-in' : 'booking';
$spaces = $connection->query('SELECT name, seats FROM spaces WHERE active = 1 ORDER BY name')->fetchAll();
$spaceCapacity = [];
foreach ($spaces as $row) $spaceCapacity[$row['name']] = (int)$row['seats'];
$availableSpaces = array_keys($spaceCapacity);

$requestedSpace = trim($_GET['space'] ?? '');
$space = in_array($requestedSpace, $availableSpaces, true) ? $requestedSpace : '';
$visitDate = $bookingType === 'walk-in' ? appToday() : '';
$visitTime = $bookingType === 'walk-in' ? appNow()->format('H:i') : '';
$durationMinutes = 60;
$guestCount = 1;
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $bookingType = ($_POST['booking_type'] ?? 'booking') === 'walk-in' ? 'walk-in' : 'booking';
    $space = trim($_POST['space'] ?? '');
    $durationMinutes = max(30, min(480, (int)($_POST['duration_minutes'] ?? 60)));
    $guestCount = max(1, min(50, (int)($_POST['guest_count'] ?? 1)));
    $notes = trim($_POST['notes'] ?? '');

    if ($bookingType === 'walk-in') {
        $visitDate = appToday();
        $visitTime = appNow()->format('H:i');
    } else {
        $visitDate = trim($_POST['visit_date'] ?? '');
        $visitTime = trim($_POST['visit_time'] ?? '');
    }

    if ($space === '' || $visitDate === '' || $visitTime === '') $errors[] = 'Please complete the space, date, and time fields.';
    if ($space !== '' && !isset($spaceCapacity[$space])) $errors[] = 'Please choose an available space.';
    if ($space !== '' && isset($spaceCapacity[$space]) && $guestCount > $spaceCapacity[$space]) $errors[] = 'This space supports up to ' . $spaceCapacity[$space] . ' guest(s).';

    $dateObject = DateTime::createFromFormat('Y-m-d', $visitDate);
    if ($visitDate !== '' && (!$dateObject || $dateObject->format('Y-m-d') !== $visitDate)) {
        $errors[] = 'Please choose a valid date.';
    } elseif ($visitDate !== '' && $visitDate < appToday()) {
        $errors[] = 'Choose today or a future date.';
    }

    $timeObject = DateTime::createFromFormat('H:i', $visitTime);
    if ($visitTime !== '' && (!$timeObject || $timeObject->format('H:i') !== $visitTime)) $errors[] = 'Please choose a valid time.';

    if (!$errors && bookingHasConflict($connection, $space, $visitDate, $visitTime, $durationMinutes)) {
        $errors[] = 'That space is already reserved during the selected time. Please choose another time or space.';
    }

    if (!$errors) {
        if ($bookingType === 'walk-in') {
            $now = appNow()->format('Y-m-d H:i:s');
            $statement = $connection->prepare('INSERT INTO bookings (user_id, booking_type, space, visit_date, visit_time, duration_minutes, guest_count, notes, status, checked_in_at, updated_at) VALUES (?, "walk-in", ?, ?, ?, ?, ?, ?, "Checked in", ?, ?)');
            $statement->execute([$user['id'], $space, $visitDate, $visitTime, $durationMinutes, $guestCount, $notes, $now, $now]);
            header('Location: ../Dashboard/index.php?checkedin=1&booking=' . (int)$connection->lastInsertId());
        } else {
            $statement = $connection->prepare('INSERT INTO bookings (user_id, booking_type, space, visit_date, visit_time, duration_minutes, guest_count, notes, updated_at) VALUES (?, "booking", ?, ?, ?, ?, ?, ?, ?)');
            $statement->execute([$user['id'], $space, $visitDate, $visitTime, $durationMinutes, $guestCount, $notes, appNow()->format('Y-m-d H:i:s')]);
            header('Location: ../Dashboard/index.php?booked=1&booking=' . (int)$connection->lastInsertId());
        }
        exit;
    }
}

$pageTitle = $bookingType === 'walk-in' ? 'Walk-in Check-in | LFT Dumaguete' : 'Book a Space | LFT Dumaguete';
$currentPage = 'BOOKING';
require '../Includes/header.php';
?>
<main><section class="auth-section"><div class="booking-card">
    <div><p class="section-label"><?= $bookingType === 'walk-in' ? 'WALK-IN CHECK-IN' : 'RESERVE YOUR SPACE' ?></p>
    <h1><?= $bookingType === 'walk-in' ? 'Check in now.' : 'Plan your next workday.' ?></h1>
    <p>Hi <?= e($user['name']) ?>. <?= $bookingType === 'walk-in' ? 'Choose an available workspace and we will check you in immediately.' : 'Choose your workspace and visit time. Staff will review the request.' ?></p>
    <p class="booking-links"><a href="?type=booking">Book a space</a> <span>/</span> <a href="?type=walk-in">Walk-in check-in</a></p></div>

    <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
    <?php if (!$availableSpaces): ?><div class="form-error">No spaces are currently available. Please contact the LFT team.</div><?php else: ?>
    <form class="tour-form" method="post">
        <?= csrfField() ?>
        <input type="hidden" name="booking_type" value="<?= e($bookingType) ?>">
        <label for="space">Space</label>
        <select id="space" name="space" required><option value="">Choose a space</option><?php foreach ($spaces as $spaceRow): ?><option value="<?= e($spaceRow['name']) ?>" <?= $space === $spaceRow['name'] ? 'selected' : '' ?>><?= e($spaceRow['name']) ?> (up to <?= (int)$spaceRow['seats'] ?>)</option><?php endforeach; ?></select>
        <?php if ($bookingType === 'booking'): ?>
            <label for="visit_date">Date</label><input id="visit_date" name="visit_date" type="date" min="<?= e(appToday()) ?>" value="<?= e($visitDate) ?>" required>
            <label for="visit_time">Start time</label><input id="visit_time" name="visit_time" type="time" value="<?= e($visitTime) ?>" required>
        <?php else: ?><div class="success-message"><i class="fa-solid fa-location-dot"></i> Walk-in time will use the current Dumaguete date and time.</div><?php endif; ?>
        <label for="duration_minutes">Duration</label>
        <select id="duration_minutes" name="duration_minutes"><option value="60" <?= $durationMinutes === 60 ? 'selected' : '' ?>>1 hour</option><option value="120" <?= $durationMinutes === 120 ? 'selected' : '' ?>>2 hours</option><option value="180" <?= $durationMinutes === 180 ? 'selected' : '' ?>>3 hours</option><option value="240" <?= $durationMinutes === 240 ? 'selected' : '' ?>>4 hours</option><option value="480" <?= $durationMinutes === 480 ? 'selected' : '' ?>>8 hours</option></select>
        <label for="guest_count">Guests</label><input id="guest_count" name="guest_count" type="number" min="1" max="50" value="<?= (int)$guestCount ?>" required>
        <label for="notes">Notes <span>(optional)</span></label><textarea id="notes" name="notes" rows="4" maxlength="1000" placeholder="Equipment needs or anything our team should know."><?= e($notes) ?></textarea>
        <button class="btn btn-green" type="submit"><?= $bookingType === 'walk-in' ? 'CHECK IN NOW' : 'SUBMIT BOOKING REQUEST' ?></button>
        <a class="text-link" href="../Dashboard/index.php">Back to my dashboard</a>
    </form><?php endif; ?>
</div></section></main>
<?php require '../Includes/footer.php'; ?>
