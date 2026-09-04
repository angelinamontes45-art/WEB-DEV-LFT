<?php
require '../_guard.php';
$connection = db();
$allowedStatuses = ['Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$message = $_GET['message'] ?? '';

if (!in_array($statusFilter, $allowedStatuses, true)) $statusFilter = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking-status') {
	$status = $_POST['status'] ?? '';
	$bookingId = (int) ($_POST['booking_id'] ?? 0);
	if (in_array($status, $allowedStatuses, true) && $bookingId > 0) {
		$statement = $connection->prepare('UPDATE bookings SET status = ? WHERE id = ?');
		$statement->execute([$status, $bookingId]);
		header('Location: index.php?message=' . urlencode('Booking status updated.'));
		exit;
	}
}

$conditions = [];
$parameters = [];
if ($statusFilter !== '') {
	$conditions[] = 'bookings.status = ?';
	$parameters[] = $statusFilter;
}
if ($search !== '') {
	$conditions[] = '(users.name LIKE ? OR users.email LIKE ? OR bookings.space LIKE ?)';
	$term = '%' . $search . '%';
	$parameters[] = $term;
	$parameters[] = $term;
	$parameters[] = $term;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$statement = $connection->prepare("SELECT bookings.*, users.name, users.email FROM bookings JOIN users ON users.id = bookings.user_id $where ORDER BY bookings.visit_date ASC, bookings.visit_time ASC, bookings.created_at DESC");
$statement->execute($parameters);
$bookings = $statement->fetchAll();
$total = (int) $connection->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$pageTitle = 'Bookings | LFT Admin';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="../../assets/css/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></head><body><main class="admin-app"><?php require "../sidebar.php"; ?><div class="admin-main"><header class="admin-topbar"><div><p class="section-label">LFT DUMAGUETE</p><h1>Bookings</h1><p>Review requests, confirm visits, and manage check-ins.</p></div><?php require '../profile.php'; ?></header><?php if ($message): ?><div class="success-message"><?= e($message) ?></div><?php endif; ?><section class="admin-kpis booking-summary"><article><span>All requests</span><strong><?= $total ?></strong></article><article><span>Pending review</span><strong><?= (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn() ?></strong></article><article><span>Confirmed</span><strong><?= (int) $connection->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'")->fetchColumn() ?></strong></article></section><section class="admin-widget booking-panel"><div class="widget-heading"><div><h2>Customer requests</h2><p><?= count($bookings) ?> matching <?= count($bookings) === 1 ? 'booking' : 'bookings' ?></p></div></div><form class="booking-filters" method="get"><label for="booking-search">Search</label><input id="booking-search" name="q" value="<?= e($search) ?>" placeholder="Customer, email, or space"><label for="booking-status">Status</label><select id="booking-status" name="status"><option value="">All statuses</option><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-green" type="submit">FILTER</button><?php if ($search !== '' || $statusFilter !== ''): ?><a class="text-link" href="index.php">Clear</a><?php endif; ?></form><div class="booking-table"><div class="booking-table-head"><span>Customer</span><span>Visit</span><span>Space</span><span>Status</span><span>Actions</span></div><?php if (!$bookings): ?><div class="empty-state">No bookings match these filters.</div><?php else: foreach ($bookings as $booking): ?><div class="booking-table-row"><div><strong><?= e($booking['name']) ?></strong><small><?= e($booking['email']) ?></small></div><div><strong><?= e(date('M j, Y', strtotime($booking['visit_date']))) ?></strong><small><?= e(date('g:i A', strtotime($booking['visit_time']))) ?></small></div><span><?= e($booking['space']) ?><small><?= e(ucfirst($booking['booking_type'])) ?></small></span><form method="post" class="booking-status-form"><input type="hidden" name="action" value="booking-status"><input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>"><select name="status" aria-label="Status for <?= e($booking['name']) ?>" onchange="this.form.submit()"><?php foreach ($allowedStatuses as $status): ?><option <?= $booking['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><noscript><button class="text-link" type="submit">Save</button></noscript></form><div class="row-actions"><a class="text-link" href="view.php?id=<?= (int) $booking['id'] ?>">View</a><form method="post" action="delete.php" onsubmit="return confirm('Delete this booking?');"><input type="hidden" name="id" value="<?= (int) $booking['id'] ?>"><button class="danger-link" type="submit">Delete</button></form></div></div><?php endforeach; endif; ?></div></section></div></main></body></html>
