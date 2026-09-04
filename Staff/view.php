<?php
require '../Includes/db.php';
$user = requireRole(['staff', 'admin']);
$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$bookingId = (int) ($_GET['id'] ?? 0);
$message = '';

$statement = $connection->prepare('SELECT bookings.*, users.name, users.email FROM bookings JOIN users ON users.id = bookings.user_id WHERE bookings.id = ?');
$statement->execute([$bookingId]);
$booking = $statement->fetch();

if (!$booking) {
    header('Location: bookings.php?message=' . urlencode('Booking not found.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $allowedStatuses, true)) {
        $update = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $bookingId]);
        $booking['status'] = $newStatus;
        $message = 'Booking status updated.';
    }
}

$pageTitle = 'Booking Details | LFT Staff';
$currentStaffPage = 'bookings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<main class="admin-app">
    <?php require '../Admin/sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">STAFF OPERATIONS</p>
                <h1>Booking details</h1>
                <p>Review the guest request and update its status.</p>
            </div>
            <?php require '../Admin/profile.php'; ?>
        </header>

        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i><?= e($message) ?></div><?php endif; ?>

        <section class="staff-panel staff-panel-wide">
            <div class="widget-heading">
                <div>
                    <span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span>
                    <h2><?= e($booking['space']) ?></h2>
                    <p><?= e(date('F j, Y', strtotime($booking['visit_date']))) ?> at <?= e(date('g:i A', strtotime($booking['visit_time']))) ?></p>
                </div>
                <a class="text-link" href="bookings.php">Back to booking queue</a>
            </div>

            <div class="booking-card">
                <p><strong>Guest:</strong> <?= e($booking['name']) ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?= e($booking['email']) ?>"><?= e($booking['email']) ?></a></p>
                <p><strong>Status:</strong> <?= e($booking['status']) ?></p>
                <p><strong>Created:</strong> <?= e(date('M j, Y g:i A', strtotime($booking['created_at']))) ?></p>
                <p><strong>Notes:</strong><br><?= $booking['notes'] !== '' ? nl2br(e($booking['notes'])) : 'No notes provided.' ?></p>
            </div>

            <form class="tour-form" method="post">
                <label for="status">Update status</label>
                <select id="status" name="status">
                    <?php foreach ($allowedStatuses as $item): ?>
                        <option value="<?= e($item) ?>" <?= $booking['status'] === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-green" type="submit">SAVE STATUS</button>
            </form>
        </section>
    </div>
</main>
</body>
</html>
