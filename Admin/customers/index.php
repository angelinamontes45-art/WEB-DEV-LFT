<?php
require '../_guard.php';

$connection = db();
$search = trim($_GET['q'] ?? '');

$conditions = ["users.role = 'customer'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(users.name LIKE ? OR users.email LIKE ?)';
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
}

$sql = "SELECT users.id, users.name, users.email, users.created_at,
               COUNT(bookings.id) AS booking_count,
               SUM(CASE WHEN bookings.status IN ('Pending','Confirmed','Checked in') THEN 1 ELSE 0 END) AS active_count,
               MAX(bookings.created_at) AS last_booking_at
        FROM users
        LEFT JOIN bookings ON bookings.user_id = users.id
        WHERE " . implode(' AND ', $conditions) . "
        GROUP BY users.id
        ORDER BY users.created_at DESC";
$statement = $connection->prepare($sql);
$statement->execute($params);
$customers = $statement->fetchAll();

$stats = [
    'customers' => (int) $connection->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'withBookings' => (int) $connection->query("SELECT COUNT(DISTINCT users.id) FROM users JOIN bookings ON bookings.user_id = users.id WHERE users.role = 'customer'")->fetchColumn(),
    'newThisMonth' => (int) $connection->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= datetime('now','start of month')")->fetchColumn(),
];

$pageTitle = 'Customers | LFT Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<main class="admin-app">
    <?php require '../sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">COMMUNITY</p>
                <h1>Customers</h1>
                <p>See registered members and their booking activity.</p>
            </div>
            <?php require '../profile.php'; ?>
        </header>

        <section class="admin-kpis">
            <article><i class="fa-solid fa-users"></i><span>Total customers</span><strong><?= $stats['customers'] ?></strong><small>Registered accounts</small></article>
            <article><i class="fa-regular fa-calendar-check"></i><span>Customers with bookings</span><strong><?= $stats['withBookings'] ?></strong><small>Have used reservations</small></article>
            <article><i class="fa-solid fa-user-plus"></i><span>New this month</span><strong><?= $stats['newThisMonth'] ?></strong><small>Recent registrations</small></article>
        </section>

        <section class="admin-widget booking-panel">
            <div class="widget-heading">
                <div><h2>Customer directory</h2><p><?= count($customers) ?> shown</p></div>
                <a href="../staff/">Manage staff accounts</a>
            </div>

            <form class="booking-filters" method="get">
                <label for="customer-search">Search</label>
                <input id="customer-search" name="q" value="<?= e($search) ?>" placeholder="Name or email">
                <button class="btn btn-green" type="submit">SEARCH</button>
                <?php if ($search !== ''): ?><a class="text-link" href="index.php">Clear</a><?php endif; ?>
            </form>

            <div class="booking-table">
                <div class="message-table-head"><span>Customer</span><span>Email</span><span>Joined</span><span>Bookings</span><span>Active</span><span>Action</span></div>
                <?php if (!$customers): ?>
                    <div class="empty-state">No customers found.</div>
                <?php else: foreach ($customers as $customer): ?>
                    <div class="message-table-row">
                        <strong><?= e($customer['name']) ?></strong>
                        <span><?= e($customer['email']) ?></span>
                        <span><?= e(date('M j, Y', strtotime($customer['created_at']))) ?></span>
                        <span><?= (int) $customer['booking_count'] ?></span>
                        <span><?= (int) $customer['active_count'] ?></span>
                        <a class="text-link" href="../bookings/?q=<?= urlencode($customer['email']) ?>">View bookings</a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </div>
</main>
</body>
</html>
