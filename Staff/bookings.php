<?php
require '../Includes/db.php';
$user = requireRole(['staff', 'admin']);
$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$message = $_GET['message'] ?? '';

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if ($bookingId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $update = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $bookingId]);
        header('Location: bookings.php?message=' . urlencode('Booking status updated.'));
        exit;
    }
}

$conditions = [];
$params = [];
if ($status !== '') {
    $conditions[] = 'bookings.status = ?';
    $params[] = $status;
}
if ($search !== '') {
    $conditions[] = '(users.name LIKE ? OR users.email LIKE ? OR bookings.space LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}

$sql = 'SELECT bookings.*, users.name, users.email
        FROM bookings
        JOIN users ON users.id = bookings.user_id '
        . ($conditions ? 'WHERE ' . implode(' AND ', $conditions) . ' ' : '')
        . 'ORDER BY bookings.visit_date ASC, bookings.visit_time ASC';
$statement = $connection->prepare($sql);
$statement->execute($params);
$bookings = $statement->fetchAll();

$counts = [];
foreach ($allowedStatuses as $item) {
    $countStatement = $connection->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
    $countStatement->execute([$item]);
    $counts[$item] = (int) $countStatement->fetchColumn();
}

$pageTitle = 'Staff Bookings | LFT Dumaguete';
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
                <h1>Booking queue</h1>
                <p>Review requests, check guests in, and complete visits.</p>
            </div>
            <?php require '../Admin/profile.php'; ?>
        </header>

        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i><?= e($message) ?></div><?php endif; ?>

        <section class="staff-kpis">
            <article class="staff-kpi"><span>Pending</span><strong><?= $counts['Pending'] ?></strong><small>Needs review</small></article>
            <article class="staff-kpi"><span>Confirmed</span><strong><?= $counts['Confirmed'] ?></strong><small>Upcoming visits</small></article>
            <article class="staff-kpi"><span>Checked in</span><strong><?= $counts['Checked in'] ?></strong><small>Currently on site</small></article>
            <article class="staff-kpi"><span>Completed</span><strong><?= $counts['Completed'] ?></strong><small>Finished visits</small></article>
        </section>

        <section class="staff-panel staff-panel-wide">
            <div class="widget-heading"><div><h2>All booking requests</h2><p><?= count($bookings) ?> result<?= count($bookings) === 1 ? '' : 's' ?></p></div></div>

            <form class="booking-filters" method="get">
                <label for="staff-booking-search">Search</label>
                <input id="staff-booking-search" name="q" value="<?= e($search) ?>" placeholder="Guest, email, or space">
                <label for="staff-booking-status">Status</label>
                <select id="staff-booking-status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($allowedStatuses as $item): ?>
                        <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-green" type="submit">FILTER</button>
                <?php if ($search !== '' || $status !== ''): ?><a class="text-link" href="bookings.php">Clear</a><?php endif; ?>
            </form>

            <?php if (!$bookings): ?>
                <div class="empty-state">No bookings match this filter.</div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <article class="staff-row">
                        <div>
                            <span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span>
                            <h3><?= e($booking['space']) ?></h3>
                            <small><?= e($booking['name']) ?> · <?= e($booking['email']) ?></small>
                        </div>
                        <div>
                            <strong><?= e(date('M j, Y', strtotime($booking['visit_date']))) ?></strong>
                            <span><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></span>
                        </div>
                        <form method="post">
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                            <select name="status" aria-label="Update booking status">
                                <?php foreach ($allowedStatuses as $item): ?>
                                    <option value="<?= e($item) ?>" <?= $booking['status'] === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-green" type="submit">SAVE</button>
                        </form>
                        <a class="text-link" href="view.php?id=<?= (int) $booking['id'] ?>">View details</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
