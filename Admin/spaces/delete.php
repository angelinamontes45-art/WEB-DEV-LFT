<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$statement = db()->prepare('DELETE FROM spaces WHERE id = ?');
	$statement->execute([(int) ($_POST['id'] ?? 0)]);
}
adminRedirect('spaces', 'message=' . urlencode('Space deleted.'));
