<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$statement = db()->prepare('DELETE FROM bookings WHERE id = ?');
	$statement->execute([(int) ($_POST['id'] ?? 0)]);
}
adminRedirect('bookings', 'message=' . urlencode('Booking deleted.'));
