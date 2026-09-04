<?php
require '../_guard.php';

$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$bookingId = (int) ($_GET['id'] ?? 0);
$message = $_GET['message'] ?? '';
$error = '';

$statement = $connection->prepare(
    'SELECT bookings.*, users.name AS customer_name, users.email AS customer_email
     FROM bookings
     JOIN users ON users.id = bookings.user_id
     WHERE bookings.id = ?'
);
$statement->execute([$bookingId]);
$booking = $statement->fetch();

if (!$booking) {
    header('Location: index.php?message=' . urlencode('Booking not found.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking-status') {
    $status = $_POST['status'] ?? '';
    if (in_array($status, $allowedStatuses, true)) {
        $update = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $update->execute([$status, $bookingId]);
        header('Location: view.php?id=' . $bookingId . '&message=' . urlencode('Booking status updated.'));
        exit;
    }
    $error = 'Choose a valid booking status.';
}

$pageTitle = 'Booking #' . $bookingId . ' | LFT Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<main class="admin-app">
    <?php require '../sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">BOOKING #<?= $bookingId ?></p>
                <h1><?= e($booking['space']) ?></h1>
                <p>Review the complete customer request and manage its status.</p>
            </div>
            <?php require '../profile.php'; ?>
        </header>

        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <?= e($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>

        <section class="admin-widget booking-detail">
            <div class="widget-heading">
                <div>
                    <span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span>
                    <h2><?= e($booking['customer_name']) ?></h2>
                    <p><?= e($booking['customer_email']) ?></p>
                </div>
                <a class="text-link" href="index.php">Back to bookings</a>
            </div>

            <div class="booking-detail-grid">
                <div class="booking-card">
                    <p><strong>Space</strong><br><?= e($booking['space']) ?></p>
                    <p><strong>Visit date</strong><br><?= e(date('F j, Y', strtotime($booking['visit_date']))) ?></p>
                    <p><strong>Visit time</strong><br><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></p>
                    <p><strong>Booking type</strong><br><?= e(ucfirst($booking['booking_type'])) ?></p>
                    <p><strong>Current status</strong><br><span class="table-status status-<?= e(strtolower(str_replace(' ', '-', $booking['status']))) ?>"><?= e($booking['status']) ?></span></p>
                    <p><strong>Submitted</strong><br><?= e(date('M j, Y g:i A', strtotime($booking['created_at']))) ?></p>
                </div>

                <div class="booking-card">
                    <p><strong>Customer</strong><br><?= e($booking['customer_name']) ?></p>
                    <p><strong>Email</strong><br><a class="text-link" href="mailto:<?= e($booking['customer_email']) ?>"><?= e($booking['customer_email']) ?></a></p>
                    <p><strong>Notes</strong></p>
                    <p><?= trim((string) $booking['notes']) !== '' ? nl2br(e($booking['notes'])) : 'No notes provided.' ?></p>
                </div>
            </div>

            <form class="admin-editor" method="post">
                <input type="hidden" name="action" value="booking-status">
                <label for="status">Update booking status
                    <select id="status" name="status" required>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?= e($status) ?>" <?= $booking['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-green" type="submit">SAVE STATUS</button>
            </form>

            <div class="dashboard-actions" style="margin-top: 22px; justify-content: flex-start;">
                <a class="text-link" href="mailto:<?= e($booking['customer_email']) ?>?subject=<?= rawurlencode('LFT booking #' . $bookingId) ?>">Email customer</a>
                <form method="post" action="delete.php" onsubmit="return confirm('Permanently delete this booking?');">
                    <input type="hidden" name="id" value="<?= $bookingId ?>">
                    <button class="danger-link" type="submit">Delete booking</button>
                </form>
            </div>
        </section>
    </div>
</main>
</body>
</html>
