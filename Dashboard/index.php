<?php
require '../Includes/db.php';
$user = requireUser();
$connection = db();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $statement = $connection->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status IN ('Pending', 'Confirmed')");
    $statement->execute([$bookingId, $user['id']]);
    if ($statement->rowCount() > 0) {
        $message = 'Your booking was cancelled.';
    } else {
        $error = 'This booking can no longer be cancelled online.';
    }
}

$statement = $connection->prepare('SELECT id, booking_type, space, visit_date, visit_time, notes, status, created_at FROM bookings WHERE user_id = ? ORDER BY visit_date DESC, visit_time DESC');
$statement->execute([$user['id']]);
$bookings = $statement->fetchAll();

$today = date('Y-m-d');
$upcoming = [];
$history = [];
foreach ($bookings as $booking) {
    if ($booking['visit_date'] >= $today && !in_array($booking['status'], ['Completed', 'Cancelled'], true)) {
        $upcoming[] = $booking;
    } else {
        $history[] = $booking;
    }
}

$counts = [
    'upcoming' => count($upcoming),
    'pending' => count(array_filter($bookings, fn($booking) => $booking['status'] === 'Pending')),
    'completed' => count(array_filter($bookings, fn($booking) => $booking['status'] === 'Completed')),
];

$pageTitle = 'My Dashboard | LFT Dumaguete';
$currentPage = 'DASHBOARD';
require '../Includes/header.php';
?>
<main>
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-heading">
                <div>
                    <p class="section-label">MEMBER DASHBOARD</p>
                    <h1>Hello, <?= e($user['name']) ?>.</h1>
                    <p><?= e($user['email']) ?></p>
                </div>
                <div class="dashboard-actions">
                    <a class="btn btn-green" href="../Booking/index.php">BOOK A SPACE</a>
                    <a class="btn btn-outline1" href="../Booking/index.php?type=walk-in">WALK-IN CHECK-IN</a>
                    <a class="text-link" href="../Logout/index.php">Log out</a>
                </div>
            </div>

            <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i><?= e($message) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>

            <section class="staff-kpis customer-kpis">
                <article class="staff-kpi"><span>Upcoming</span><strong><?= $counts['upcoming'] ?></strong><small>Active visits</small></article>
                <article class="staff-kpi"><span>Pending</span><strong><?= $counts['pending'] ?></strong><small>Awaiting confirmation</small></article>
                <article class="staff-kpi"><span>Completed</span><strong><?= $counts['completed'] ?></strong><small>Finished visits</small></article>
                <article class="staff-kpi"><span>Account</span><strong><i class="fa-regular fa-circle-check"></i></strong><small>Ready to book</small></article>
            </section>

            <div class="dashboard-list">
                <div class="widget-heading"><div><h2>Upcoming visits</h2><p>Your active booking requests.</p></div><a class="text-link" href="../Spaces/index.php">Explore spaces</a></div>
                <?php if (!$upcoming): ?>
                    <div class="empty-state">You have no upcoming visits. <a href="../Booking/index.php">Book your next workspace.</a></div>
                <?php else: ?>
                    <?php foreach ($upcoming as $booking): ?>
                        <article class="request-row customer-request-row">
                            <div>
                                <span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span>
                                <h3><?= e($booking['space']) ?></h3>
                                <?php if ($booking['notes']): ?><small><?= e($booking['notes']) ?></small><?php endif; ?>
                            </div>
                            <div>
                                <strong><?= e(date('M j, Y', strtotime($booking['visit_date']))) ?></strong>
                                <span><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></span>
                            </div>
                            <span class="request-status"><?= e($booking['status']) ?></span>
                            <?php if (in_array($booking['status'], ['Pending', 'Confirmed'], true)): ?>
                                <form method="post" onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button class="danger-link" type="submit">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="dashboard-list dashboard-history">
                <div class="widget-heading"><div><h2>History</h2><p>Completed, cancelled, and previous visits.</p></div></div>
                <?php if (!$history): ?>
                    <div class="empty-state">Your visit history will appear here.</div>
                <?php else: ?>
                    <?php foreach ($history as $booking): ?>
                        <article class="request-row customer-request-row">
                            <div><span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span><h3><?= e($booking['space']) ?></h3></div>
                            <div><strong><?= e(date('M j, Y', strtotime($booking['visit_date']))) ?></strong><span><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></span></div>
                            <span class="request-status"><?= e($booking['status']) ?></span>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
