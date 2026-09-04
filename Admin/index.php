<?php
require '../Includes/db.php';

$user = requireRole(['admin']);
$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking-status') {
    $status = $_POST['status'] ?? '';
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    if ($bookingId > 0 && in_array($status, $allowedStatuses, true)) {
        $statement = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $statement->execute([$status, $bookingId]);
        $message = 'Booking status updated.';
    } else {
        $error = 'Invalid booking update.';
    }
}

$today = date('Y-m-d');
$stats = [
    'bookings' => (int) $connection->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
    'pending' => (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
    'today' => (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE visit_date = date('now') AND status NOT IN ('Cancelled','Completed')")->fetchColumn(),
    'customers' => (int) $connection->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'staff' => (int) $connection->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn(),
    'spaces' => (int) $connection->query('SELECT COUNT(*) FROM spaces WHERE active = 1')->fetchColumn(),
    'unread' => (int) $connection->query("SELECT COUNT(*) FROM messages WHERE status = 'Unread'")->fetchColumn(),
    'events' => (int) $connection->query("SELECT COUNT(*) FROM events WHERE active = 1 AND event_date >= date('now')")->fetchColumn(),
];

$recentBookings = $connection->query("SELECT bookings.*, users.name AS customer_name, users.email AS customer_email
    FROM bookings JOIN users ON users.id = bookings.user_id
    ORDER BY bookings.created_at DESC LIMIT 8")->fetchAll();

$todayBookings = $connection->query("SELECT bookings.*, users.name AS customer_name
    FROM bookings JOIN users ON users.id = bookings.user_id
    WHERE bookings.visit_date = date('now') AND bookings.status NOT IN ('Cancelled','Completed')
    ORDER BY bookings.visit_time ASC LIMIT 6")->fetchAll();

$recentMessages = $connection->query("SELECT id, sender, email, subject, status, created_at FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$upcomingEvents = $connection->query("SELECT * FROM events WHERE active = 1 AND event_date >= date('now') ORDER BY event_date ASC, event_time ASC LIMIT 4")->fetchAll();

$statusCounts = array_fill_keys($allowedStatuses, 0);
foreach ($connection->query('SELECT status, COUNT(*) AS total FROM bookings GROUP BY status')->fetchAll() as $row) {
    if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']] = (int) $row['total'];
}

$dailyLabels = [];
$dailyValues = [];
$dailyStatement = $connection->prepare("SELECT COUNT(*) FROM bookings WHERE date(created_at) = ?");
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime('-' . $i . ' days'));
    $dailyLabels[] = date('M j', strtotime($date));
    $dailyStatement->execute([$date]);
    $dailyValues[] = (int) $dailyStatement->fetchColumn();
}
$maxDaily = max(1, ...$dailyValues);

$pageTitle = 'Admin Dashboard | LFT Dumaguete';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<main class="admin-app">
    <?php require 'sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">LFT CONTROL CENTER · <?= e(date('l, F j')) ?></p>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= e($user['name']) ?>. Here is what needs attention today.</p>
            </div>
            <?php require 'profile.php'; ?>
        </header>

        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i> <?= e($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>

        <section class="admin-kpis">
            <article><i class="fa-regular fa-calendar-check"></i><span>Pending bookings</span><strong><?= $stats['pending'] ?></strong><small><a href="bookings/?status=Pending">Needs review</a></small></article>
            <article><i class="fa-solid fa-person-walking-arrow-right"></i><span>Today’s visits</span><strong><?= $stats['today'] ?></strong><small>Expected today</small></article>
            <article><i class="fa-regular fa-envelope"></i><span>Unread messages</span><strong><?= $stats['unread'] ?></strong><small><a href="messages/?status=Unread">Open inbox</a></small></article>
            <article><i class="fa-solid fa-users"></i><span>Customers</span><strong><?= $stats['customers'] ?></strong><small><?= $stats['staff'] ?> staff accounts</small></article>
        </section>

        <section class="admin-quick-actions">
            <a href="bookings/?status=Pending"><i class="fa-solid fa-list-check"></i><span><strong>Review pending</strong><small>Confirm or decline new requests</small></span></a>
            <a href="customers/"><i class="fa-solid fa-address-book"></i><span><strong>Customer directory</strong><small>See accounts and activity</small></span></a>
            <a href="staff/"><i class="fa-solid fa-users-gear"></i><span><strong>Manage staff</strong><small>Promote or remove staff access</small></span></a>
            <a href="spaces/"><i class="fa-solid fa-couch"></i><span><strong>Manage spaces</strong><small><?= $stats['spaces'] ?> active spaces</small></span></a>
        </section>

        <section class="admin-grid">
            <article class="admin-widget booking-chart">
                <div class="widget-heading">
                    <div><h2>Booking activity</h2><p>New requests created in the last 7 days</p></div>
                    <a href="bookings/">View all</a>
                </div>
                <div class="chart-bars">
                    <?php foreach ($dailyValues as $index => $value): ?>
                        <div class="chart-column" title="<?= $value ?> booking<?= $value === 1 ? '' : 's' ?>">
                            <span style="height: <?= max(5, (int) (($value / $maxDaily) * 100)) ?>%;"></span>
                            <small><?= e($dailyLabels[$index]) ?></small>
                            <b><?= $value ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="admin-widget status-widget">
                <div class="widget-heading"><div><h2>Booking pipeline</h2><p><?= $stats['bookings'] ?> total requests</p></div></div>
                <div class="status-list">
                    <?php foreach ($allowedStatuses as $status): ?>
                        <a href="bookings/?status=<?= urlencode($status) ?>">
                            <span class="status-dot status-<?= strtolower(str_replace(' ', '-', $status)) ?>"></span>
                            <span><?= e($status) ?></span>
                            <strong><?= $statusCounts[$status] ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>

        <section class="admin-grid lower-grid">
            <article class="admin-widget">
                <div class="widget-heading"><div><h2>Today’s arrivals</h2><p>Front-desk schedule</p></div><a href="bookings/">Booking queue</a></div>
                <?php if (!$todayBookings): ?>
                    <div class="empty-state">No active visits scheduled for today.</div>
                <?php else: ?>
                    <div class="admin-compact-list">
                    <?php foreach ($todayBookings as $booking): ?>
                        <a href="bookings/view.php?id=<?= (int) $booking['id'] ?>">
                            <div><strong><?= e($booking['customer_name']) ?></strong><small><?= e($booking['space']) ?></small></div>
                            <div><strong><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></strong><small><?= e($booking['status']) ?></small></div>
                        </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="admin-widget">
                <div class="widget-heading"><div><h2>Recent messages</h2><p>Latest visitor inquiries</p></div><a href="messages/">View inbox</a></div>
                <?php if (!$recentMessages): ?>
                    <div class="empty-state">No messages yet.</div>
                <?php else: ?>
                    <div class="admin-compact-list">
                    <?php foreach ($recentMessages as $item): ?>
                        <a href="messages/view.php?id=<?= (int) $item['id'] ?>">
                            <div><strong><?= e($item['sender']) ?></strong><small><?= e($item['subject']) ?></small></div>
                            <span class="table-status <?= $item['status'] === 'Unread' ? 'status-pending' : '' ?>"><?= e($item['status']) ?></span>
                        </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <section class="admin-widget admin-dashboard-bookings">
            <div class="widget-heading"><div><h2>Recent bookings</h2><p>Latest customer requests</p></div><a href="bookings/">View all bookings</a></div>
            <div class="admin-table">
                <div class="table-head"><span>Customer</span><span>Space</span><span>Visit</span><span>Status</span><span>Action</span></div>
                <?php if (!$recentBookings): ?><div class="empty-state">No bookings yet.</div><?php else: foreach ($recentBookings as $booking): ?>
                    <div class="table-row">
                        <span><strong><?= e($booking['customer_name']) ?></strong><small><?= e($booking['customer_email']) ?></small></span>
                        <span><?= e($booking['space']) ?></span>
                        <span><?= e(date('M j, Y', strtotime($booking['visit_date']))) ?><small><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></small></span>
                        <span class="table-status status-<?= strtolower(str_replace(' ', '-', $booking['status'])) ?>"><?= e($booking['status']) ?></span>
                        <span><a class="text-link" href="bookings/view.php?id=<?= (int) $booking['id'] ?>">View</a></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section class="admin-widget">
            <div class="widget-heading"><div><h2>Upcoming events</h2><p><?= $stats['events'] ?> future published events</p></div><a href="events/">Manage events</a></div>
            <?php if (!$upcomingEvents): ?><div class="empty-state">No upcoming events.</div><?php else: ?><div class="admin-event-grid">
                <?php foreach ($upcomingEvents as $event): ?>
                    <article><span><?= e(date('M j', strtotime($event['event_date']))) ?></span><strong><?= e($event['title']) ?></strong><small><?= e(date('g:i A', strtotime($event['event_time']))) ?> · <?= e($event['category']) ?></small></article>
                <?php endforeach; ?>
            </div><?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
