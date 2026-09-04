<?php
require '../Includes/db.php';
$user = requireRole(['staff', 'admin']);
$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['status'] ?? '', $allowedStatuses, true)) {
    $statement = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $statement->execute([$_POST['status'], (int) ($_POST['booking_id'] ?? 0)]);
    $message = 'Booking status updated.';
}

$today = date('Y-m-d');
$todayStatement = $connection->prepare(
    'SELECT bookings.*, users.name, users.email
     FROM bookings
     JOIN users ON users.id = bookings.user_id
     WHERE bookings.visit_date = ?
     ORDER BY bookings.visit_time ASC'
);
$todayStatement->execute([$today]);
$todayBookings = $todayStatement->fetchAll();
$upcoming = $connection->query(
        "SELECT bookings.*, users.name
         FROM bookings
         JOIN users ON users.id = bookings.user_id
         WHERE bookings.visit_date >= date('now')
             AND bookings.visit_date != '$today'
             AND bookings.status NOT IN ('Cancelled', 'Completed')
         ORDER BY bookings.visit_date ASC, bookings.visit_time ASC
         LIMIT 5"
)->fetchAll();
$counts = [
    'today' => count($todayBookings),
    'pending' => (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
    'checked' => (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE status = 'Checked in'")->fetchColumn(),
    'spaces' => (int) $connection->query('SELECT COUNT(*) FROM spaces WHERE active = 1')->fetchColumn(),
];
$pageTitle = 'Staff Desk | LFT Dumaguete';
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
                <p class="section-label">STAFF DESK / <?= e(date('l, F j')) ?></p>
                <h1>Good morning, <?= e($user['name']) ?>.</h1>
                <p>Keep today’s arrivals moving smoothly.</p>
            </div>
            <?php require '../Admin/profile.php'; ?>
        </header>
        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i><?= e($message) ?></div><?php endif; ?>
        <section class="staff-kpis">
            <article class="staff-kpi"><span>Today’s visits</span><strong><?= $counts['today'] ?></strong><small>Scheduled arrivals</small></article>
            <article class="staff-kpi"><span>Pending review</span><strong><?= $counts['pending'] ?></strong><small>Needs attention</small></article>
            <article class="staff-kpi"><span>Checked in</span><strong><?= $counts['checked'] ?></strong><small>Currently on site</small></article>
            <article class="staff-kpi"><span>Active spaces</span><strong><?= $counts['spaces'] ?></strong><small>Ready to welcome</small></article>
        </section>
        <div class="staff-content"><section class="staff-panel staff-panel-wide"><div class="widget-heading"><div><h2>Today’s arrivals</h2><p><?= e(date('D, M j, Y')) ?> · front desk queue</p></div><span class="table-status">LIVE QUEUE</span></div><?php if (!$todayBookings): ?><div class="empty-state">No visits scheduled for today.</div><?php else: foreach ($todayBookings as $booking): ?><article class="staff-row"><div><span class="request-type"><?= e($booking['booking_type']) ?></span><h3><?= e($booking['space']) ?></h3><small><?= e($booking['name']) ?> · <?= e($booking['email']) ?></small></div><div><strong><?= e($booking['visit_time']) ?></strong><span><?= e($booking['status']) ?></span></div><form method="post"><input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>"><select name="status" aria-label="Update booking status"><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $booking['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-green" type="submit">SAVE</button></form></article><?php endforeach; endif; ?></section>
        <section class="staff-panel"><div class="widget-heading"><div><h2>Desk checklist</h2><p>Keep the welcome experience ready.</p></div></div><div class="staff-checklist"><div><i class="fa-solid fa-check"></i><span>Review pending requests</span></div><div><i class="fa-solid fa-check"></i><span>Prepare meeting rooms</span></div><div><i class="fa-solid fa-check"></i><span>Check coffee corner</span></div></div></section>
        <section class="staff-panel"><div class="widget-heading"><div><h2>Coming up</h2><p>Next confirmed visits</p></div></div><?php if (!$upcoming): ?><div class="empty-state">Nothing upcoming.</div><?php else: ?><div class="staff-quick-links"><?php foreach ($upcoming as $booking): ?><a href="../Admin/bookings/view.php?id=<?= e((string) $booking['id']) ?>"><span><?= e($booking['space']) ?><small><?= e($booking['name']) ?></small></span><strong><?= e(date('M j', strtotime($booking['visit_date']))) ?></strong></a><?php endforeach; ?></div><?php endif; ?></section></div>
    </div>
</main>
<script>
    window.adminPath = '../Admin/';
    window.adminLogout = '../Logout/index.php';
    window.adminUser = <?= json_encode([
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => ucfirst($user['role'])
    ]) ?>;
    window.pendingBookings = <?= $counts['pending'] ?>;
</script>
<script src="../assets/js/admin.js"></script>
</body>
</html>