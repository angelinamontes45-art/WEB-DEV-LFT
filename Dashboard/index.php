<?php
require '../Includes/db.php';
$user = requireUser();
$statement = db()->prepare('SELECT booking_type, space, visit_date, visit_time, status FROM bookings WHERE user_id = ? ORDER BY visit_date DESC, visit_time DESC');
$statement->execute([$user['id']]);
$bookings = $statement->fetchAll();
$pageTitle = 'My Requests | LFT Dumaguete';
$currentPage = 'CONTACT';
require '../Includes/header.php';
?>
<main><section class="dashboard-section"><div class="container"><div class="dashboard-heading"><div><p class="section-label">MEMBER DASHBOARD</p><h1>Hello, <?= e($user['name']) ?>.</h1><p><?= e($user['email']) ?></p></div><div class="dashboard-actions"><a class="btn btn-green" href="../Booking/index.php">BOOK A VISIT</a><a class="text-link" href="../Logout/index.php">Log out</a></div></div><div class="dashboard-list"><h2>Your requests</h2><?php if (!$bookings): ?><div class="empty-state">You have no requests yet. <a href="../Booking/index.php">Book your first visit.</a></div><?php else: foreach ($bookings as $booking): ?><article class="request-row"><div><span class="request-type"><?= e(ucfirst($booking['booking_type'])) ?></span><h3><?= e($booking['space']) ?></h3></div><div><strong><?= e($booking['visit_date']) ?></strong><span><?= e($booking['visit_time']) ?></span></div><span class="request-status"><?= e($booking['status']) ?></span></article><?php endforeach; endif; ?></div></div></section></main>
<?php require '../Includes/footer.php'; ?>