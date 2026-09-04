<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') db()->prepare('DELETE FROM events WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
adminRedirect('events', 'message=' . urlencode('Event deleted.'));
